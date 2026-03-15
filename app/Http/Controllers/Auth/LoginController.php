<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Pilote;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Affiche le formulaire de connexion
     * Si l'utilisateur est déjà connecté, redirige vers le dashboard approprié
     */
    public function showLoginForm()
    {
        // Vérifier si un pilote (client) est déjà connecté
        if (Auth::guard('pilotes')->check()) {
            return redirect()->route('dashboard');
        }

        // Vérifier si un administrateur est déjà connecté
        if (Auth::guard('web')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.login');
    }

    /**
     * Traite la connexion
     * Essaie d'abord les participants (pilotes), puis les administrateurs
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $email = $request->email;
        $password = $request->password;

        // Essayer d'abord la connexion en tant que participant (pilote)
        $pilote = Pilote::where('email', $email)->first();
        
        if ($pilote) {
            // Si le pilote n'a pas de mot de passe, rediriger vers la réinitialisation
            if (!$pilote->password) {
                return redirect()->route('password.request')
                    ->with('message', 'Veuillez définir votre mot de passe pour la première fois.')
                    ->withInput(['email' => $email]);
            }

            if (Hash::check($password, $pilote->password)) {
                Auth::guard('pilotes')->login($pilote, $request->filled('remember'));
                return redirect()->route('dashboard');
            }
        }

        // Si pas de pilote ou mot de passe incorrect, essayer l'administrateur
        $user = User::where('email', $email)->where('role', 'admin')->first();
        
        if ($user && Hash::check($password, $user->password)) {
            Auth::guard('web')->login($user, $request->filled('remember'));
            return redirect()->route('admin.dashboard');
        }

        // Si aucun des deux ne fonctionne, erreur
        throw ValidationException::withMessages([
            'email' => ['Les identifiants fournis sont incorrects.'],
        ]);
    }

    /**
     * Déconnexion
     */
    public function logout(Request $request)
    {
        if (Auth::guard('pilotes')->check()) {
            Auth::guard('pilotes')->logout();
        }
        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}

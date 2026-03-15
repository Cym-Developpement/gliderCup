<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class ResetPasswordController extends Controller
{
    /**
     * Affiche le formulaire de réinitialisation
     */
    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.passwords.reset')->with([
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    /**
     * Réinitialise le mot de passe
     * Essaie d'abord les participants (pilotes), puis les administrateurs
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $email = $request->email;
        $token = $request->token;
        $password = $request->password;

        // Essayer d'abord avec les pilotes
        $status = Password::broker('pilotes')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = $password; // Hashé automatiquement par le cast
                $user->save();
            }
        );

        // Si pas trouvé dans les pilotes, essayer les administrateurs
        if ($status !== Password::PASSWORD_RESET) {
            $status = Password::broker('users')->reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $user->password = $password; // Hashé automatiquement par le cast
                    $user->save();
                }
            );
        }

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', __($status));
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }
}


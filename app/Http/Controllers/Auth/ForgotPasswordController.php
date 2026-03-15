<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    /**
     * Affiche le formulaire de demande de réinitialisation
     */
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    /**
     * Envoie le lien de réinitialisation
     * Essaie d'abord les participants (pilotes), puis les administrateurs
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->email;

        // Essayer d'abord avec les pilotes
        $status = Password::broker('pilotes')->sendResetLink(
            $request->only('email')
        );

        // Si pas trouvé dans les pilotes, essayer les administrateurs
        if ($status !== Password::RESET_LINK_SENT) {
            $status = Password::broker('users')->sendResetLink(
                $request->only('email')
            );
        }

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', __($status));
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }
}


<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Notifications\MessageContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    /**
     * Envoie un message de contact
     */
    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string|max:5000',
        ], [
            'nom.required' => 'Le nom est obligatoire.',
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email doit être valide.',
            'message.required' => 'Le message est obligatoire.',
            'message.max' => 'Le message ne doit pas dépasser 5000 caractères.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Enregistrer le message en base de données
            $contactMessage = ContactMessage::create([
                'nom' => $request->nom,
                'email' => $request->email,
                'message' => $request->message,
            ]);

            // Envoyer l'email à l'administrateur
            $adminEmail = config('mail.admin_email', 'contact@wassmercup.fr');
            
            Notification::route('mail', $adminEmail)
                ->notify(new MessageContact(
                    $request->nom,
                    $request->email,
                    $request->message
                ));

            return response()->json([
                'success' => true,
                'message' => 'Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.'
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'envoi du message de contact: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'envoi du message. Veuillez réessayer plus tard.'
            ], 500);
        }
    }
}

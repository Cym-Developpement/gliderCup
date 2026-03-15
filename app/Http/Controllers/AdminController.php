<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\Pilote;
use App\Models\Planeur;
use App\Models\PaiementConfiguration;
use App\Models\Counter;
use App\Models\ContactMessage;
use App\Models\VisiteurUnique;
use App\Notifications\InscriptionValidee;
use App\Notifications\InscriptionRefusee;
use App\Notifications\PlaneurValide;
use App\Notifications\CompteCree;
use App\Notifications\LienPaiement;
use App\Notifications\MessageEnvoye;
use App\Notifications\MessageContact;
use App\Notifications\ReponseContact;
use App\Models\Message;
use App\Models\MessageGroupe;
use App\Services\OpenAipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    /**
     * Affiche le tableau de bord administrateur
     */
    public function dashboard()
    {
        $competition = Competition::active();
        
        if (!$competition) {
            abort(404, 'Aucune compétition active trouvée.');
        }

        $inscriptionsEnAttente = $competition->pilotes()->where('statut', 'en_attente')->count();
        $inscriptionsValidees = $competition->pilotes()->where('statut', 'validee')->count();
        $inscriptionsRefusees = $competition->pilotes()->where('statut', 'refusee')->count();
        $totalPlaneurs = $competition->planeurs()->count();
        $paiementsNonValides = $competition->pilotes()->where('paiement_valide', false)->count();
        
        // Utiliser les données de la table visiteurs_uniques si disponibles, sinon fallback sur Counter
        // Chaque enregistrement représente déjà un visiteur unique (IP + user agent + date)
        $visitesAccueil = VisiteurUnique::where('competition_id', $competition->id)->count();
        $visitesAccueilAujourdhui = VisiteurUnique::where('competition_id', $competition->id)
            ->where('date_visite', date('Y-m-d'))
            ->count();
        
        // Fallback sur Counter si pas de données dans visiteurs_uniques
        if ($visitesAccueil === 0) {
            $visitesAccueil = Counter::getValue('home_visits:competition:' . $competition->id, 0);
        }
        if ($visitesAccueilAujourdhui === 0) {
            $visitesAccueilAujourdhui = Counter::getValue('home_visits:competition:' . $competition->id . ':date:' . date('Y-m-d'), 0);
        }

        $inscriptions = $competition->pilotes()->with('planeurs')->orderBy('created_at', 'desc')->paginate(20);

        // Récupérer tous les paiements HelloAsso (validés et non validés)
        // Filtrer les pilotes avec helloasso_checkout_intent_id non null et non vide
        // Note: On filtre d'abord en SQL, puis on filtre en PHP pour gérer les espaces (compatible SQLite)
        $paiementsHelloAsso = $competition->pilotes()
            ->whereNotNull('helloasso_checkout_intent_id')
            ->where('helloasso_checkout_intent_id', '!=', '')
            ->with('planeurs')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Filtrer également en PHP pour s'assurer qu'aucun champ vide ou avec seulement des espaces ne passe
        // (SQLite ne supporte pas TRIM() de la même manière dans les requêtes)
        $paiementsHelloAsso = $paiementsHelloAsso->filter(function($pilote) {
            $checkoutId = trim($pilote->helloasso_checkout_intent_id ?? '');
            return !empty($checkoutId);
        })->values();
        
        // Séparer les paiements validés et non validés pour le polling
        $paiementsHelloAssoEnAttente = $paiementsHelloAsso->filter(function($pilote) {
            return !$pilote->paiement_valide;
        })->values();

        // Récupérer les messages de contact non répondus
        $messagesContact = ContactMessage::where('repondu', false)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.dashboard', [
            'competition' => $competition,
            'inscriptionsEnAttente' => $inscriptionsEnAttente,
            'inscriptionsValidees' => $inscriptionsValidees,
            'inscriptionsRefusees' => $inscriptionsRefusees,
            'totalPlaneurs' => $totalPlaneurs,
            'paiementsNonValides' => $paiementsNonValides,
            'visitesAccueil' => $visitesAccueil,
            'visitesAccueilAujourdhui' => $visitesAccueilAujourdhui,
            'inscriptions' => $inscriptions,
            'paiementsHelloAsso' => $paiementsHelloAsso,
            'paiementsHelloAssoEnAttente' => $paiementsHelloAssoEnAttente,
            'messagesContact' => $messagesContact,
        ]);
    }

    /**
     * Valide une inscription
     */
    public function validerInscription($id)
    {
        $pilote = Pilote::with(['planeurs.piloteProprietaire'])->findOrFail($id);
        $pilote->statut = 'validee';
        $pilote->save();

        // Récupérer tous les planeurs associés
        $planeurs = $pilote->planeurs;

        // Valider tous les planeurs associés et envoyer les emails aux propriétaires
        $proprietairesNotifies = [];
        foreach ($planeurs as $planeur) {
            // Valider le planeur
            $planeur->statut = 'validee';
            $planeur->save();

            // Récupérer le pilote propriétaire du planeur
            $piloteProprietaire = $planeur->piloteProprietaire;

            // Envoyer un email au propriétaire si différent du pilote inscrit et pas déjà notifié
            if ($piloteProprietaire && 
                $piloteProprietaire->id !== $pilote->id && 
                !in_array($piloteProprietaire->id, $proprietairesNotifies)) {
                $piloteProprietaire->notify(new PlaneurValide($planeur, $pilote));
                $proprietairesNotifies[] = $piloteProprietaire->id;
            }
        }

        // Envoyer l'email de confirmation au pilote inscrit avec tous les planeurs
        $pilote->notify(new InscriptionValidee($pilote, $planeurs));

        $message = 'L\'inscription a été validée avec succès. Un email de confirmation a été envoyé au participant.';
        if (count($proprietairesNotifies) > 0) {
            $message .= ' Les propriétaires des planeurs ont également été notifiés.';
        }

        return redirect()->route('admin.dashboard')
            ->with('success', $message);
    }

    /**
     * Refuse une inscription
     */
    public function refuserInscription($id)
    {
        $pilote = Pilote::findOrFail($id);
        $pilote->statut = 'refusee';
        $pilote->save();

        // Envoyer l'email de notification au pilote
        $pilote->notify(new InscriptionRefusee($pilote));

        return redirect()->route('admin.dashboard')
            ->with('success', 'L\'inscription a été refusée. Un email de notification a été envoyé au participant.');
    }

    /**
     * Supprime complètement une inscription refusée et toutes ses données associées
     */
    public function supprimerInscription($id)
    {
        $pilote = Pilote::with(['planeurs', 'messages', 'planeursProprietaire'])->findOrFail($id);

        // Vérifier que l'inscription est bien refusée
        if ($pilote->statut !== 'refusee') {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Seules les inscriptions refusées peuvent être supprimées complètement.');
        }

        // Supprimer les fichiers du pilote
        $fichiersPilote = [
            'autorisation_parentale',
            'feuille_declarative_qualifications',
            'visite_medicale_classe_2',
            'spl_valide',
        ];

        foreach ($fichiersPilote as $fichier) {
            if ($pilote->$fichier && Storage::disk('public')->exists($pilote->$fichier)) {
                Storage::disk('public')->delete($pilote->$fichier);
            }
        }

        // Supprimer les fichiers des messages associés avant suppression
        foreach ($pilote->messages as $message) {
            if ($message->piece_jointe && Storage::disk('public')->exists($message->piece_jointe)) {
                Storage::disk('public')->delete($message->piece_jointe);
            }
        }

        // Supprimer les fichiers des planeurs dont le pilote est propriétaire avant suppression
        foreach ($pilote->planeursProprietaire as $planeur) {
            if ($planeur->cdn_cen && Storage::disk('public')->exists($planeur->cdn_cen)) {
                Storage::disk('public')->delete($planeur->cdn_cen);
            }
            if ($planeur->responsabilite_civile && Storage::disk('public')->exists($planeur->responsabilite_civile)) {
                Storage::disk('public')->delete($planeur->responsabilite_civile);
            }
        }

        // Supprimer le pilote lui-même
        // Les contraintes de clé étrangère (onDelete('cascade')) s'occupent automatiquement de :
        // - Supprimer les messages associés
        // - Supprimer les relations many-to-many dans pilote_planeur
        // - Supprimer les planeurs dont le pilote est propriétaire
        $pilote->delete();

        return redirect()->route('admin.dashboard')
            ->with('success', 'L\'inscription a été supprimée complètement avec toutes ses données associées.');
    }

    /**
     * Exporte la liste des pilotes
     */
    public function exporterPilotes()
    {
        $competition = Competition::active();
        
        if (!$competition) {
            abort(404, 'Aucune compétition active trouvée.');
        }

        $pilotes = $competition->pilotes()->with('planeurs')->get();

        $filename = 'pilotes_' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($pilotes) {
            $file = fopen('php://output', 'w');
            
            // BOM UTF-8 pour Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // En-têtes
            fputcsv($file, [
                'ID', 'Nom', 'Prénom', 'Email', 'Qualité', 'Date de naissance',
                'Téléphone', 'N° FFVP', 'Club', 'Statut', 'Date d\'inscription'
            ], ';');
            
            // Données
            foreach ($pilotes as $pilote) {
                fputcsv($file, [
                    $pilote->id,
                    $pilote->nom,
                    $pilote->prenom,
                    $pilote->email,
                    $pilote->qualite,
                    $pilote->date_naissance->format('d/m/Y'),
                    $pilote->telephone ?? '',
                    $pilote->numero_ffvp ?? '',
                    $pilote->club ?? '',
                    $pilote->statut,
                    $pilote->created_at->format('d/m/Y H:i')
                ], ';');
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Exporte la liste des planeurs
     */
    public function exporterPlaneurs()
    {
        $competition = Competition::active();
        
        if (!$competition) {
            abort(404, 'Aucune compétition active trouvée.');
        }

        $planeurs = $competition->planeurs()->with('piloteProprietaire')->get();

        $filename = 'planeurs_' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($planeurs) {
            $file = fopen('php://output', 'w');
            
            // BOM UTF-8 pour Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // En-têtes
            fputcsv($file, [
                'ID', 'Immatriculation', 'Marque', 'Modèle', 'Type',
                'Pilote propriétaire', 'Email pilote', 'Date d\'inscription'
            ], ';');
            
            // Données
            foreach ($planeurs as $planeur) {
                $piloteNom = '';
                if ($planeur->piloteProprietaire) {
                    $piloteNom = $planeur->piloteProprietaire->prenom . ' ' . $planeur->piloteProprietaire->nom;
                }
                
                fputcsv($file, [
                    $planeur->id,
                    $planeur->immatriculation,
                    $planeur->marque ?? '',
                    $planeur->modele,
                    $planeur->type ?? '',
                    $piloteNom,
                    $planeur->piloteProprietaire->email ?? '',
                    $planeur->created_at->format('d/m/Y H:i')
                ], ';');
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Affiche la liste des planeurs inscrits
     */
    public function listePlaneurs()
    {
        $competition = Competition::active();
        
        if (!$competition) {
            abort(404, 'Aucune compétition active trouvée.');
        }

        $planeurs = $competition->planeurs()
            ->with(['piloteProprietaire', 'pilotes'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.planeurs', [
            'competition' => $competition,
            'planeurs' => $planeurs,
            'totalPlaneurs' => $competition->planeurs()->count(),
            'limitePlaneurs' => $competition->limite_planeurs,
        ]);
    }

    /**
     * Récupère les détails complets d'un pilote
     */
    public function getPiloteDetails($id)
    {
        $pilote = Pilote::with(['planeurs.piloteProprietaire'])->findOrFail($id);
        
        // Calculer l'âge pour déterminer si le pilote est mineur
        $age = \Carbon\Carbon::parse($pilote->date_naissance)->age;
        $estMineur = $age < 18;
        
        return response()->json([
            'pilote' => [
                'id' => $pilote->id,
                'nom' => $pilote->nom,
                'prenom' => $pilote->prenom,
                'qualite' => $pilote->qualite,
                'date_naissance' => $pilote->date_naissance->format('d/m/Y'),
                'age' => $age,
                'est_mineur' => $estMineur,
                'email' => $pilote->email,
                'telephone' => $pilote->telephone,
                'club' => $pilote->club,
                'adresse' => $pilote->adresse,
                'code_postal' => $pilote->code_postal,
                'ville' => $pilote->ville,
                'numero_ffvp' => $pilote->numero_ffvp,
                'statut' => $pilote->statut,
                'paiement_valide' => $pilote->paiement_valide,
                'identifiant_virement' => $pilote->identifiant_virement,
                'helloasso_checkout_intent_id' => $pilote->helloasso_checkout_intent_id,
                'created_at' => $pilote->created_at->format('d/m/Y H:i'),
                'documents' => [
                    'autorisation_parentale' => $pilote->autorisation_parentale,
                    'feuille_declarative_qualifications' => $pilote->feuille_declarative_qualifications,
                    'visite_medicale_classe_2' => $pilote->visite_medicale_classe_2,
                    'spl_valide' => $pilote->spl_valide,
                ],
            ],
            'planeurs' => $pilote->planeurs->map(function($planeur) {
                return [
                    'id' => $planeur->id,
                    'marque' => $planeur->marque,
                    'modele' => $planeur->modele,
                    'type' => $planeur->type,
                    'immatriculation' => $planeur->immatriculation,
                    'proprietaire' => $planeur->piloteProprietaire ? $planeur->piloteProprietaire->prenom . ' ' . $planeur->piloteProprietaire->nom : null,
                    'documents' => [
                        'cdn_cen' => $planeur->cdn_cen,
                        'responsabilite_civile' => $planeur->responsabilite_civile,
                    ],
                ];
            }),
        ]);
    }

    /**
     * Envoie un message au pilote pour l'informer que son compte est créé
     */
    public function envoyerMessageCompteCree($id)
    {
        $pilote = Pilote::findOrFail($id);
        
        // Envoyer la notification
        $pilote->notify(new CompteCree($pilote));

        return redirect()->route('admin.dashboard')
            ->with('success', 'Le message de création de compte a été envoyé avec succès à ' . $pilote->email . '.');
    }

    /**
     * Envoie le lien de paiement au pilote
     */
    public function envoyerLienPaiement($id)
    {
        $pilote = Pilote::findOrFail($id);
        
        // Envoyer la notification avec le lien de paiement
        $pilote->notify(new LienPaiement($pilote));

        return redirect()->route('admin.dashboard')
            ->with('success', 'Le lien de paiement a été envoyé avec succès à ' . $pilote->email . '.');
    }

    /**
     * Remplace un document d'un pilote
     */
    public function remplacerDocument(Request $request, $id)
    {
        $pilote = Pilote::findOrFail($id);

        $request->validate([
            'type_document' => 'required|in:autorisation_parentale,feuille_declarative_qualifications,visite_medicale_classe_2,spl_valide',
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:102400',
        ], [
            'type_document.required' => 'Le type de document est requis.',
            'type_document.in' => 'Type de document invalide.',
            'document.required' => 'Le fichier est requis.',
            'document.file' => 'Le document doit être un fichier.',
            'document.mimes' => 'Le document doit être au format PDF, JPG, JPEG ou PNG.',
            'document.max' => 'Le document ne doit pas dépasser 100 Mo.',
        ]);

        $typeDocument = $request->type_document;
        
        // Fonction helper pour gérer l'upload de fichiers
        $uploadFile = function($file, $directory) {
            if ($file && $file->isValid()) {
                $filename = time() . '_' . $file->getClientOriginalName();
                return $file->storeAs($directory, $filename, 'public');
            }
            return null;
        };

        // Déterminer le répertoire selon le type de document
        $directory = ($typeDocument === 'autorisation_parentale') ? 'autorisations' : 'documents';

        // Supprimer l'ancien fichier s'il existe
        $ancienFichier = $pilote->$typeDocument;
        if ($ancienFichier && Storage::disk('public')->exists($ancienFichier)) {
            Storage::disk('public')->delete($ancienFichier);
        }

        // Uploader le nouveau fichier
        $nouveauFichier = $uploadFile($request->file('document'), $directory);
        
        if ($nouveauFichier) {
            $pilote->$typeDocument = $nouveauFichier;
            $pilote->save();

            return redirect()->route('admin.dashboard')
                ->with('success', 'Le document a été remplacé avec succès.');
        } else {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Erreur lors de l\'upload du document.');
        }
    }

    /**
     * Affiche la configuration de paiement
     */
    public function getPaiementConfiguration()
    {
        $config = PaiementConfiguration::getConfiguration();
        return response()->json($config);
    }

    /**
     * Met à jour la configuration de paiement
     */
    public function updatePaiementConfiguration(Request $request)
    {
        $request->validate([
            'adresse_cheque' => 'nullable|string',
            'iban_virement' => 'nullable|string|max:34',
            'bic_virement' => 'nullable|string|max:11',
        ], [
            'iban_virement.max' => 'L\'IBAN ne doit pas dépasser 34 caractères.',
            'bic_virement.max' => 'Le BIC ne doit pas dépasser 11 caractères.',
        ]);

        $config = PaiementConfiguration::getConfiguration();
        $config->update($request->only([
            'adresse_cheque',
            'iban_virement',
            'bic_virement',
        ]));

        return redirect()->route('admin.dashboard')
            ->with('success', 'Configuration de paiement mise à jour avec succès.');
    }

    /**
     * Valide le paiement d'un pilote
     */
    public function validerPaiement($id)
    {
        $pilote = Pilote::findOrFail($id);
        $pilote->paiement_valide = true;
        $pilote->save();

        return redirect()->route('admin.dashboard')
            ->with('success', 'Le paiement de ' . $pilote->prenom . ' ' . $pilote->nom . ' a été validé avec succès.');
    }

    /**
     * Invalide le paiement d'un pilote
     */
    public function invaliderPaiement($id)
    {
        $pilote = Pilote::findOrFail($id);
        $pilote->paiement_valide = false;
        $pilote->save();

        return redirect()->route('admin.dashboard')
            ->with('success', 'Le paiement de ' . $pilote->prenom . ' ' . $pilote->nom . ' a été invalidé.');
    }

    /**
     * Affiche l'historique des messages d'un pilote
     */
    public function getMessages($id)
    {
        $pilote = Pilote::findOrFail($id);
        $messages = $pilote->messages()->with('user')->orderBy('created_at', 'desc')->get();

        return response()->json([
            'pilote' => [
                'id' => $pilote->id,
                'nom' => $pilote->nom,
                'prenom' => $pilote->prenom,
                'email' => $pilote->email,
            ],
            'messages' => $messages->map(function ($message) {
                return [
                    'id' => $message->id,
                    'message' => $message->message,
                    'piece_jointe' => $message->piece_jointe,
                    'user_name' => $message->user ? $message->user->name : 'Système',
                    'created_at' => $message->created_at->format('d/m/Y à H:i'),
                    'lu' => $message->lu,
                ];
            }),
        ]);
    }

    /**
     * Envoie un message à un pilote
     */
    public function envoyerMessage(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string|min:1',
            'piece_jointe' => 'nullable|file|max:10240', // 10MB max
        ]);

        $pilote = Pilote::findOrFail($id);

        $pieceJointe = null;
        if ($request->hasFile('piece_jointe')) {
            $file = $request->file('piece_jointe');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('messages/pieces_jointes', $filename, 'public');
            $pieceJointe = $path;
        }

        $message = Message::create([
            'pilote_id' => $pilote->id,
            'user_id' => Auth::id(),
            'message' => $request->message,
            'piece_jointe' => $pieceJointe,
            'lu' => false,
        ]);

        // Envoyer une notification par email
        $pilote->notify(new MessageEnvoye($message));

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    /**
     * Envoie un message à tous les inscrits
     */
    public function envoyerMessageGroupe(Request $request)
    {
        $request->validate([
            'message' => 'required|string|min:1',
            'sujet' => 'nullable|string|max:255',
            'piece_jointe' => 'nullable|file|max:10240', // 10MB max
        ]);

        $competition = Competition::active();
        if (!$competition) {
            return response()->json([
                'success' => false,
                'error' => 'Aucune compétition active trouvée.',
            ], 404);
        }

        $pilotes = $competition->pilotes()->get();
        
        $pieceJointe = null;
        if ($request->hasFile('piece_jointe')) {
            $file = $request->file('piece_jointe');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('messages/pieces_jointes', $filename, 'public');
            $pieceJointe = $path;
        }

        // Créer le message groupe
        $messageGroupe = MessageGroupe::create([
            'user_id' => Auth::id(),
            'message' => $request->message,
            'piece_jointe' => $pieceJointe,
            'sujet' => $request->sujet,
            'nombre_destinataires' => $pilotes->count(),
        ]);

        // Créer un message pour chaque pilote et envoyer la notification
        foreach ($pilotes as $pilote) {
            $message = Message::create([
                'pilote_id' => $pilote->id,
                'user_id' => Auth::id(),
                'message' => $request->message,
                'piece_jointe' => $pieceJointe,
                'lu' => false,
            ]);

            $pilote->notify(new MessageEnvoye($message));
        }

        return response()->json([
            'success' => true,
            'message' => 'Message envoyé à ' . $pilotes->count() . ' pilote(s).',
            'message_groupe' => $messageGroupe,
        ]);
    }

    /**
     * Liste tous les messages groupés
     */
    public function listeMessagesGroupes()
    {
        $messagesGroupes = MessageGroupe::with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'messages' => $messagesGroupes->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'sujet' => $msg->sujet,
                    'message' => $msg->message,
                    'piece_jointe' => $msg->piece_jointe,
                    'user_name' => $msg->user ? $msg->user->name : 'Système',
                    'nombre_destinataires' => $msg->nombre_destinataires,
                    'created_at' => $msg->created_at->format('d/m/Y à H:i'),
                ];
            }),
        ]);
    }

    /**
     * Met à jour le règlement de la compétition
     */
    /**
     * Répondre à un message de contact
     */
    public function repondreMessageContact(Request $request, $id)
    {
        $request->validate([
            'reponse' => 'required|string|max:5000',
        ], [
            'reponse.required' => 'La réponse est obligatoire.',
            'reponse.max' => 'La réponse ne doit pas dépasser 5000 caractères.',
        ]);

        $contactMessage = ContactMessage::findOrFail($id);

        try {
            // Envoyer l'email de réponse
            Notification::route('mail', $contactMessage->email)
                ->notify(new ReponseContact(
                    $contactMessage->nom,
                    $contactMessage->message,
                    $request->reponse
                ));

            // Enregistrer la réponse en base
            $contactMessage->repondu = true;
            $contactMessage->reponse = $request->reponse;
            $contactMessage->user_id = Auth::id();
            $contactMessage->reponse_envoyee_at = now();
            $contactMessage->save();

            return redirect()->route('admin.dashboard')
                ->with('success', 'La réponse a été envoyée avec succès.');
        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'envoi de la réponse au message de contact: ' . $e->getMessage());
            
            return redirect()->route('admin.dashboard')
                ->with('error', 'Une erreur est survenue lors de l\'envoi de la réponse. Veuillez réessayer.');
        }
    }

    public function updateReglement(Request $request)
    {
        $request->validate([
            'reglement' => 'nullable|file|mimes:pdf|max:10240', // 10MB max
        ]);

        $competition = Competition::active();
        if (!$competition) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Aucune compétition active trouvée.');
        }

        if ($request->hasFile('reglement')) {
            // Supprimer l'ancien fichier s'il existe
            if ($competition->reglement && Storage::disk('public')->exists($competition->reglement)) {
                Storage::disk('public')->delete($competition->reglement);
            }

            // Uploader le nouveau fichier
            $file = $request->file('reglement');
            $filename = 'reglement_' . time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('reglements', $filename, 'public');
            
            $competition->reglement = $path;
            $competition->save();

            return redirect()->route('admin.dashboard')
                ->with('success', 'Le règlement a été mis à jour avec succès.');
        } else {
            // Supprimer le règlement si aucun fichier n'est fourni
            if ($competition->reglement && Storage::disk('public')->exists($competition->reglement)) {
                Storage::disk('public')->delete($competition->reglement);
            }
            
            $competition->reglement = null;
            $competition->save();

            return redirect()->route('admin.dashboard')
                ->with('success', 'Le règlement a été supprimé avec succès.');
        }
    }

    /**
     * Recherche un aérodrome via l'API OpenAIP
     */
    public function searchAirport(Request $request)
    {
        Log::info('=== DÉBUT RECHERCHE AÉRODROME (Controller) ===', [
            'request_data' => $request->all(),
            'icao_raw' => $request->icao ?? 'N/A',
        ]);

        $request->validate([
            'icao' => 'required|string|max:10',
        ]);

        $icaoCode = strtoupper(trim($request->icao));
        
        Log::info('Code ICAO nettoyé', [
            'icao_original' => $request->icao,
            'icao_cleaned' => $icaoCode,
        ]);
        
        $airportData = OpenAipService::searchAirportByIcao($icaoCode);
        
        Log::info('Résultat de la recherche', [
            'icao' => $icaoCode,
            'found' => $airportData !== null,
            'data_keys' => $airportData ? array_keys($airportData) : 'N/A',
        ]);
        
        if (!$airportData) {
            Log::warning('Aérodrome non trouvé dans le contrôleur', [
                'icao' => $icaoCode,
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Aérodrome non trouvé pour le code ICAO: ' . $icaoCode,
            ], 404);
        }

        Log::info('✅ Aérodrome trouvé et retourné', [
            'icao' => $icaoCode,
            'name' => $airportData['name'] ?? 'N/A',
        ]);

        return response()->json([
            'success' => true,
            'airport' => $airportData,
        ]);
    }

    /**
     * Met à jour le code aérodrome de la compétition et recherche les informations via OpenAIP
     */
    public function updateCodeAeroport(Request $request)
    {
        $request->validate([
            'code_aeroport' => 'nullable|string|max:10',
        ], [
            'code_aeroport.max' => 'Le code aérodrome ne doit pas dépasser 10 caractères.',
        ]);

        $competition = Competition::active();
        if (!$competition) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Aucune compétition active trouvée.');
        }

        $codeAeroport = $request->code_aeroport ? strtoupper(trim($request->code_aeroport)) : null;
        
        // Si un code aéroport est fourni, rechercher les informations via OpenAIP
        if ($codeAeroport) {
            $airportData = OpenAipService::searchAirportByIcao($codeAeroport);
            
            if ($airportData) {
                // Stocker les données dans un fichier JSON
                $filename = 'airports/' . $codeAeroport . '.json';
                
                try {
                    // Créer le répertoire s'il n'existe pas
                    $directory = 'airports';
                    if (!Storage::disk('private')->exists($directory)) {
                        Storage::disk('private')->makeDirectory($directory);
                    }
                    
                    // Stocker les données en JSON
                    Storage::disk('private')->put($filename, json_encode($airportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    
                    Log::info('Données aérodrome stockées', [
                        'icao' => $codeAeroport,
                        'filename' => $filename,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Erreur lors du stockage des données aérodrome', [
                        'icao' => $codeAeroport,
                        'error' => $e->getMessage(),
                    ]);
                    
                    // Continuer quand même à sauvegarder le code
                    // mais informer l'utilisateur de l'erreur
                    $competition->code_aeroport = $codeAeroport;
                    $competition->save();
                    
                    return redirect()->route('admin.dashboard')
                        ->with('warning', 'Le code aérodrome a été enregistré, mais une erreur est survenue lors du stockage des données détaillées.');
                }
            } else {
                // Aérodrome non trouvé, mais on peut quand même sauvegarder le code
                Log::warning('Aérodrome non trouvé via OpenAIP', ['icao' => $codeAeroport]);
            }
        } else {
            // Si le code est supprimé, supprimer aussi le fichier JSON associé
            if ($competition->code_aeroport) {
                $oldFilename = 'airports/' . $competition->code_aeroport . '.json';
                if (Storage::disk('private')->exists($oldFilename)) {
                    Storage::disk('private')->delete($oldFilename);
                }
            }
        }

        $competition->code_aeroport = $codeAeroport;
        $competition->save();

        $message = $codeAeroport 
            ? 'Le code aérodrome a été mis à jour avec succès' . (isset($airportData) && $airportData ? ' et les données ont été récupérées depuis OpenAIP.' : '.')
            : 'Le code aérodrome a été supprimé avec succès.';

        return redirect()->route('admin.dashboard')
            ->with('success', $message);
    }

    /**
     * Récupère les données stockées d'un aérodrome
     */
    public function getAirportData($icaoCode)
    {
        $icaoCode = strtoupper(trim($icaoCode));
        $filename = 'airports/' . $icaoCode . '.json';
        
        if (Storage::disk('private')->exists($filename)) {
            $data = json_decode(Storage::disk('private')->get($filename), true);
            return response()->json([
                'success' => true,
                'airport' => $data,
            ]);
        }
        
        return response()->json([
            'success' => false,
            'error' => 'Données aérodrome non trouvées',
        ], 404);
    }
}

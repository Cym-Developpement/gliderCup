<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\Pilote;
use App\Models\Planeur;
use App\Notifications\ConfirmationInscription;
use App\Notifications\NouvelleInscriptionAdmin;
use App\Notifications\CompteCree;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class InscriptionController extends Controller
{
    /**
     * Affiche la page d'inscription
     */
    public function index()
    {
        $competition = Competition::active();
        
        if (!$competition) {
            abort(404, 'Aucune compétition active trouvée.');
        }

        $limitePlaneurs = $competition->limite_planeurs;
        $nombrePlaneursInscrits = $competition->planeurs()->count();
        $nombrePilotesInscrits = $competition->pilotes()->count();
        $placesRestantes = max(0, $limitePlaneurs - $nombrePlaneursInscrits);
        $planeursExistants = $competition->planeurs()
            ->with('piloteProprietaire')
            ->orderBy('immatriculation')
            ->get()
            ->map(function ($planeur) {
                return [
                    'id' => $planeur->id,
                    'label' => $planeur->immatriculation . ' - ' . ($planeur->marque ? $planeur->marque . ' ' : '') . $planeur->modele . ($planeur->piloteProprietaire ? ' (' . $planeur->piloteProprietaire->prenom . ' ' . $planeur->piloteProprietaire->nom . ')' : ''),
                ];
            });
        
        $afficherRubanStats = env('SHOW_STATS_BANNER', false) || ($nombrePilotesInscrits > 10 && $nombrePlaneursInscrits > 3);
        
        return view('inscription', [
            'competition' => $competition,
            'placesRestantes' => $placesRestantes,
            'complet' => $placesRestantes === 0,
            'planeursExistants' => $planeursExistants,
            'nombrePlaneursInscrits' => $nombrePlaneursInscrits,
            'nombrePilotesInscrits' => $nombrePilotesInscrits,
            'limitePlaneurs' => $limitePlaneurs,
            'afficherRubanStats' => $afficherRubanStats,
        ]);
    }

    /**
     * Traite le formulaire d'inscription
     */
    public function store(Request $request)
    {
        $competition = Competition::active();
        
        if (!$competition) {
            return redirect('/')
                ->withErrors(['general' => 'Aucune compétition active trouvée.'])
                ->withInput();
        }

        // Vérifier si le pilote est mineur (moins de 18 ans) et l'âge minimum
        $dateNaissance = $request->date_naissance;
        $estMineur = false;
        $age = null;
        if ($dateNaissance) {
            $age = \Carbon\Carbon::parse($dateNaissance)->age;
            $estMineur = $age < 18;
        }

        // Date maximale pour avoir au moins 14 ans (date de naissance doit être <= il y a 14 ans)
        $dateMaxNaissance = \Carbon\Carbon::now()->subYears(14)->format('Y-m-d');

        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'qualite' => 'required|in:Pilote,Élève Pilote,Instructeur',
            'date_naissance' => [
                'required',
                'date',
                'before:today',
                function ($attribute, $value, $fail) use ($dateMaxNaissance) {
                    if ($value && $value > $dateMaxNaissance) {
                        $fail('Vous devez avoir au moins 14 ans pour participer à cet événement.');
                    }
                },
            ],
            'email' => 'required|email|unique:pilotes,email',
            'telephone' => 'nullable|string|max:20',
            'club' => 'nullable|string|max:255',
            'adresse' => 'nullable|string|max:255',
            'code_postal' => 'nullable|string|max:10',
            'ville' => 'nullable|string|max:255',
            'numero_ffvp' => 'required|string|max:50',
            'autorisation_parentale' => $estMineur ? 'required|file|mimes:pdf,jpg,jpeg,png|max:102400' : 'nullable|file|mimes:pdf,jpg,jpeg,png|max:102400',
            'feuille_declarative_qualifications' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:102400',
            'visite_medicale_classe_2' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:102400',
            'spl_valide' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:102400',
            'type_inscription_planeur' => 'nullable|in:existant,nouveau',
            'cdn_cen' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:102400',
            'responsabilite_civile' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:102400',
            'planeur_existant_id' => [
                'nullable',
                'required_if:type_inscription_planeur,existant',
                function ($attribute, $value, $fail) use ($competition) {
                    if ($value && !$competition->planeurs()->where('id', $value)->exists()) {
                        $fail('Le planeur sélectionné n\'existe pas pour cette compétition.');
                    }
                },
            ],
            'modele' => 'nullable|required_if:type_inscription_planeur,nouveau|string|max:255',
            'marque' => 'nullable|string|max:255',
            'type' => 'nullable|in:plastique,bois & toiles',
            'immatriculation' => [
                'nullable',
                'required_if:type_inscription_planeur,nouveau',
                'string',
                'max:20',
                function ($attribute, $value, $fail) use ($competition) {
                    if ($value && $competition->planeurs()->where('immatriculation', $value)->exists()) {
                        $fail('Cette immatriculation est déjà enregistrée pour cette compétition.');
                    }
                },
            ],
        ], [
            'email.unique' => 'Cet e-mail est déjà inscrit.',
            'immatriculation.unique' => 'Cette immatriculation est déjà enregistrée.',
            'nom.required' => 'Le nom est obligatoire.',
            'prenom.required' => 'Le prénom est obligatoire.',
            'qualite.required' => 'La qualité est obligatoire.',
            'qualite.in' => 'La qualité sélectionnée n\'est pas valide.',
            'date_naissance.required' => 'La date de naissance est obligatoire.',
            'date_naissance.date' => 'La date de naissance doit être une date valide.',
            'date_naissance.before' => 'La date de naissance doit être antérieure à aujourd\'hui.',
            'email.required' => 'L\'e-mail est obligatoire.',
            'numero_ffvp.required' => 'Le N° FFVP est obligatoire.',
            'numero_ffvp.string' => 'Le N° FFVP doit être une chaîne de caractères.',
            'numero_ffvp.max' => 'Le N° FFVP ne doit pas dépasser 50 caractères.',
            'type_inscription_planeur.in' => 'Le type d\'inscription sélectionné n\'est pas valide.',
            'planeur_existant_id.required_if' => 'Veuillez sélectionner un planeur existant.',
            'planeur_existant_id.exists' => 'Le planeur sélectionné n\'existe pas.',
            'modele.required_if' => 'Le modèle est obligatoire pour inscrire un nouveau planeur.',
            'type.in' => 'Le type sélectionné n\'est pas valide.',
            'immatriculation.required_if' => 'L\'immatriculation est obligatoire pour inscrire un nouveau planeur.',
            'autorisation_parentale.required' => 'L\'autorisation parentale est obligatoire pour les mineurs.',
            'autorisation_parentale.file' => 'L\'autorisation parentale doit être un fichier.',
            'autorisation_parentale.mimes' => 'L\'autorisation parentale doit être au format PDF, JPG, JPEG ou PNG.',
            'autorisation_parentale.max' => 'L\'autorisation parentale ne doit pas dépasser 100 Mo.',
            'feuille_declarative_qualifications.file' => 'La feuille déclarative doit être un fichier.',
            'feuille_declarative_qualifications.mimes' => 'La feuille déclarative doit être au format PDF, JPG, JPEG ou PNG.',
            'feuille_declarative_qualifications.max' => 'La feuille déclarative ne doit pas dépasser 100 Mo.',
            'visite_medicale_classe_2.file' => 'La visite médicale doit être un fichier.',
            'visite_medicale_classe_2.mimes' => 'La visite médicale doit être au format PDF, JPG, JPEG ou PNG.',
            'visite_medicale_classe_2.max' => 'La visite médicale ne doit pas dépasser 100 Mo.',
            'spl_valide.file' => 'Le document SPL doit être un fichier.',
            'spl_valide.mimes' => 'Le document SPL doit être au format PDF, JPG, JPEG ou PNG.',
            'spl_valide.max' => 'Le document SPL ne doit pas dépasser 100 Mo.',
            'cdn_cen.file' => 'Le CDN/CEN doit être un fichier.',
            'cdn_cen.mimes' => 'Le CDN/CEN doit être au format PDF, JPG, JPEG ou PNG.',
            'cdn_cen.max' => 'Le CDN/CEN ne doit pas dépasser 100 Mo.',
            'responsabilite_civile.file' => 'La responsabilité civile doit être un fichier.',
            'responsabilite_civile.mimes' => 'La responsabilité civile doit être au format PDF, JPG, JPEG ou PNG.',
            'responsabilite_civile.max' => 'La responsabilité civile ne doit pas dépasser 100 Mo.',
        ]);

        if ($validator->fails()) {
            return redirect('/')
                ->withErrors($validator)
                ->withInput();
        }

        // Fonction helper pour gérer l'upload de fichiers
        $uploadFile = function($file, $directory) {
            if ($file) {
                $filename = time() . '_' . $file->getClientOriginalName();
                return $file->storeAs($directory, $filename, 'public');
            }
            return null;
        };

        // Gérer l'upload de l'autorisation parentale si présente
        $autorisationPath = null;
        if ($request->hasFile('autorisation_parentale')) {
            $autorisationPath = $uploadFile($request->file('autorisation_parentale'), 'autorisations');
        }

        // Gérer l'upload des documents facultatifs
        $feuilleDeclarativePath = null;
        if ($request->hasFile('feuille_declarative_qualifications')) {
            $feuilleDeclarativePath = $uploadFile($request->file('feuille_declarative_qualifications'), 'documents');
        }

        $visiteMedicalePath = null;
        if ($request->hasFile('visite_medicale_classe_2')) {
            $visiteMedicalePath = $uploadFile($request->file('visite_medicale_classe_2'), 'documents');
        }

        $splValidePath = null;
        if ($request->hasFile('spl_valide')) {
            $splValidePath = $uploadFile($request->file('spl_valide'), 'documents');
        }

        // Créer le pilote avec statut "en_attente" (sans mot de passe, il sera défini lors de la première connexion)
        // Générer un identifiant unique de 8 caractères (lettres et chiffres)
        $genererIdentifiantUnique = function() {
            $caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            do {
                $identifiant = '';
                for ($i = 0; $i < 8; $i++) {
                    $identifiant .= $caracteres[random_int(0, strlen($caracteres) - 1)];
                }
            } while (Pilote::where('identifiant_virement', $identifiant)->exists());
            return $identifiant;
        };

        $pilote = Pilote::create([
            'competition_id' => $competition->id,
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'qualite' => $request->qualite,
            'date_naissance' => $request->date_naissance,
            'email' => $request->email,
            'password' => null, // Pas de mot de passe à l'inscription
            'identifiant_virement' => $genererIdentifiantUnique(),
            'telephone' => $request->telephone,
            'club' => $request->club,
            'adresse' => $request->adresse,
            'code_postal' => $request->code_postal,
            'ville' => $request->ville,
            'numero_ffvp' => $request->numero_ffvp,
            'autorisation_parentale' => $autorisationPath,
            'feuille_declarative_qualifications' => $feuilleDeclarativePath,
            'visite_medicale_classe_2' => $visiteMedicalePath,
            'spl_valide' => $splValidePath,
            'statut' => 'en_attente',
        ]);

        // Gérer l'upload des documents du planeur
        $cdnCenPath = null;
        if ($request->hasFile('cdn_cen')) {
            $cdnCenPath = $uploadFile($request->file('cdn_cen'), 'documents');
        }

        $responsabiliteCivilePath = null;
        if ($request->hasFile('responsabilite_civile')) {
            $responsabiliteCivilePath = $uploadFile($request->file('responsabilite_civile'), 'documents');
        }

        // Gérer l'inscription du planeur
        $planeur = null;
        if ($request->filled('type_inscription_planeur')) {
            if ($request->type_inscription_planeur === 'existant') {
                // Associer le pilote à un planeur existant de cette compétition
                $planeur = $competition->planeurs()->findOrFail($request->planeur_existant_id);
                
                // Mettre à jour les documents du planeur existant si fournis
                if ($cdnCenPath) {
                    $planeur->cdn_cen = $cdnCenPath;
                }
                if ($responsabiliteCivilePath) {
                    $planeur->responsabilite_civile = $responsabiliteCivilePath;
                }
                if ($cdnCenPath || $responsabiliteCivilePath) {
                    $planeur->save();
                }

                $pilote->planeurs()->attach($planeur->id);
            } elseif ($request->type_inscription_planeur === 'nouveau') {
                // Vérifier qu'il reste des places disponibles
                $limitePlaneurs = $competition->limite_planeurs;
                $nombrePlaneursInscrits = $competition->planeurs()->count();
                if ($nombrePlaneursInscrits >= $limitePlaneurs) {
                    return redirect('/')
                        ->withErrors(['type_inscription_planeur' => 'Désolé, toutes les places pour les planeurs sont déjà prises (' . $limitePlaneurs . ' planeurs maximum).'])
                        ->withInput();
                }

                // Créer un nouveau planeur
                $planeur = Planeur::create([
                    'competition_id' => $competition->id,
                    'pilote_id' => $pilote->id,
                    'modele' => $request->modele,
                    'marque' => $request->marque,
                    'type' => $request->type,
                    'immatriculation' => $request->immatriculation,
                    'statut' => 'en_attente', // Statut initial en attente
                    'cdn_cen' => $cdnCenPath,
                    'responsabilite_civile' => $responsabiliteCivilePath,
                ]);
                
                // Associer le pilote propriétaire au planeur
                $pilote->planeurs()->attach($planeur->id);
            }
        }

        // 1. Envoyer l'e-mail de récapitulatif au pilote (avec détails complets y compris planeur)
        $pilote->notify(new ConfirmationInscription($pilote, $planeur));

        // 2. Envoyer une copie à tous les administrateurs
        $admins = \App\Models\User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new NouvelleInscriptionAdmin($pilote, $planeur));
        }
        
        // Fallback : envoyer aussi à l'email configuré si défini
        $adminEmail = config('mail.admin_email');
        if ($adminEmail && $admins->isEmpty()) {
            \Illuminate\Support\Facades\Notification::route('mail', $adminEmail)
                ->notify(new NouvelleInscriptionAdmin($pilote, $planeur));
        }

        // 3. Envoyer l'e-mail de création de compte au pilote
        $pilote->notify(new CompteCree($pilote));

        // 4. Rediriger vers la page de paiement avec l'identifiant unique
        $identifiant = $pilote->identifiant_virement;
        return redirect()->route('paiement.public', ['identifiantVirement' => $identifiant])
            ->with('success', 'Votre inscription a été enregistrée avec succès ! Elle est actuellement en attente de validation par un administrateur. Veuillez procéder au paiement pour finaliser votre inscription.');
    }

    /**
     * Affiche le règlement de la compétition
     */
    public function reglement()
    {
        try {
            $competition = Competition::active();
            
            if (!$competition) {
                \Log::warning('Tentative d\'accès au règlement sans compétition active');
                abort(404, 'Aucune compétition active trouvée.');
            }
            
            if (!$competition->reglement) {
                \Log::warning('Tentative d\'accès au règlement sans fichier', [
                    'competition_id' => $competition->id,
                ]);
                abort(404, 'Règlement non disponible pour cette compétition.');
            }

            // Utiliser Storage pour vérifier l'existence et obtenir le chemin
            $reglementPath = $competition->reglement;
            
            // Si le fichier enregistré n'existe pas, chercher le dernier fichier dans le dossier reglements
            if (!Storage::disk('public')->exists($reglementPath)) {
                $allReglements = Storage::disk('public')->allFiles('reglements');
                
                if (empty($allReglements)) {
                    \Log::error('Aucun règlement trouvé', [
                        'competition_id' => $competition->id,
                        'reglement_path' => $competition->reglement,
                        'storage_path' => storage_path('app/public'),
                    ]);
                    abort(404, 'Aucun fichier de règlement trouvé sur le serveur.');
                }
                
                // Trier par date de modification (le plus récent en premier) et prendre le premier
                $reglementFiles = collect($allReglements)->map(function ($file) {
                    return [
                        'path' => $file,
                        'time' => Storage::disk('public')->lastModified($file),
                    ];
                })->sortByDesc('time')->values();
                
                $reglementPath = $reglementFiles->first()['path'];
                
                // Mettre à jour la base de données avec le nouveau chemin
                $competition->reglement = $reglementPath;
                $competition->save();
                
                \Log::info('Règlement mis à jour automatiquement', [
                    'competition_id' => $competition->id,
                    'ancien_path' => $competition->reglement,
                    'nouveau_path' => $reglementPath,
                ]);
            }

            // Obtenir le chemin complet via Storage
            $filePath = Storage::disk('public')->path($reglementPath);

            if (!is_readable($filePath)) {
                \Log::error('Règlement non lisible', [
                    'competition_id' => $competition->id,
                    'file_path' => $filePath,
                    'permissions' => file_exists($filePath) ? substr(sprintf('%o', fileperms($filePath)), -4) : 'N/A',
                ]);
                abort(500, 'Le fichier du règlement n\'est pas accessible.');
            }

            return response()->file($filePath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="reglement.pdf"',
            ]);
        } catch (\Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException $e) {
            \Log::error('Fichier règlement introuvable', [
                'error' => $e->getMessage(),
            ]);
            abort(404, 'Fichier du règlement non trouvé.');
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la lecture du règlement', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            abort(500, 'Erreur lors de la lecture du fichier.');
        }
    }

    /**
     * Récupère les données stockées d'un aérodrome (route publique)
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
        
        // Vérifier si le code aérodrome est configuré dans la compétition
        $competition = Competition::active();
        $codeConfigure = $competition && $competition->code_aeroport === $icaoCode;
        
        return response()->json([
            'success' => false,
            'error' => $codeConfigure 
                ? 'Les données de l\'aérodrome n\'ont pas encore été récupérées. Veuillez configurer le code aérodrome dans le dashboard admin et cliquer sur "Rechercher" puis "Enregistrer".'
                : 'Données aérodrome non trouvées. Le code aérodrome doit être configuré dans le dashboard admin.',
            'code_configure' => $codeConfigure,
        ], 404);
    }
}

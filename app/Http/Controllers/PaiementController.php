<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\Pilote;
use App\Models\PaiementConfiguration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class PaiementController extends Controller
{
    /**
     * Affiche la page de paiement
     */
    public function index(Request $request, $identifiantVirement = null)
    {
        // Si l'utilisateur est connecté, utiliser son compte
        $pilote = null;
        $isTestMode = false;
        
        if (Auth::guard('pilotes')->check()) {
            $pilote = Auth::guard('pilotes')->user();
        } elseif ($identifiantVirement) {
            // Vérifier si c'est un identifiant de test (commence par "TEST-")
            if (str_starts_with($identifiantVirement, 'TEST-')) {
                // Mode test : créer un pilote fictif
                $competition = Competition::active();
                $pilote = (object)[
                    'id' => 999999,
                    'nom' => 'Dupont',
                    'prenom' => 'Jean',
                    'email' => 'test@example.com',
                    'identifiant_virement' => $identifiantVirement,
                ];
                $isTestMode = true;
            } else {
                // Rechercher par identifiant_virement au lieu de l'ID
                $pilote = Pilote::with(['competition', 'planeurs'])
                    ->where('identifiant_virement', $identifiantVirement)
                    ->first();
            }
        } elseif ($request->has('identifiant')) {
            $identifiant = $request->identifiant;
            // Vérifier si c'est un identifiant de test
            if (str_starts_with($identifiant, 'TEST-')) {
                $competition = Competition::active();
                $pilote = (object)[
                    'id' => 999999,
                    'nom' => 'Dupont',
                    'prenom' => 'Jean',
                    'email' => 'test@example.com',
                    'identifiant_virement' => $identifiant,
                ];
                $isTestMode = true;
            } else {
                $pilote = Pilote::with(['competition', 'planeurs'])
                    ->where('identifiant_virement', $identifiant)
                    ->first();
            }
        }

        if (!$pilote) {
            return redirect('/')->with('error', 'Pilote non trouvé ou identifiant invalide.');
        }
        
        // Recharger le pilote pour avoir les données à jour (seulement si ce n'est pas un objet fictif)
        // Important : recharger depuis la base pour éviter les problèmes de cache
        if (!$isTestMode && $pilote instanceof Pilote && isset($pilote->id)) {
            $pilote = Pilote::with(['competition', 'planeurs'])->find($pilote->id);
            if (!$pilote) {
                return redirect('/')->with('error', 'Pilote non trouvé.');
            }
        }

        $competition = (!$isTestMode && isset($pilote->competition)) ? $pilote->competition : Competition::active();
        
        // Pour le mode test, utiliser 0 planeurs, sinon compter les planeurs du pilote
        if ($isTestMode) {
            $nombrePlaneurs = 0;
        } else {
            $nombrePlaneurs = method_exists($pilote, 'planeurs') ? $pilote->planeurs()->count() : 0;
        }
        
        // Calculer le montant total
        $montantPlaneur = 50; // 50€ par planeur
        $montantAdhesion = 50; // 50€ d'adhésion par pilote
        if ($isTestMode) {
            $montantTotal = 1.00;
        } elseif ($pilote instanceof Pilote && $pilote->montant_custom !== null) {
            $montantTotal = (float) $pilote->montant_custom;
        } else {
            $montantTotal = ($nombrePlaneurs * $montantPlaneur) + $montantAdhesion;
        }

        // Informations de paiement depuis la base de données (ou .env en fallback)
        $config = PaiementConfiguration::getConfiguration();
        $adresseCheque = $config->adresse_cheque ?: env('PAIEMENT_CHEQUE_ADRESSE', '');
        $ibanVirement = $config->iban_virement ?: env('PAIEMENT_VIREMENT_IBAN', '');
        $bicVirement = $config->bic_virement ?: env('PAIEMENT_VIREMENT_BIC', '');
        // Utiliser uniquement l'identifiant de 8 caractères, sans préfixe ni tiret
        $referenceVirement = $pilote->identifiant_virement ?? str_pad($pilote->id, 8, '0', STR_PAD_LEFT);
        
        // Générer l'URL du checkout HelloAsso via l'API
        $helloAssoCheckoutUrl = null;
        $helloAssoError = null;
        
        // Vérifier si HELLO_ASSO_USE_SANDBOX_ALL est activé (force le mode sandbox pour tous les paiements)
        $useSandboxAll = env('HELLO_ASSO_USE_SANDBOX_ALL', false);
        
        // Si HELLO_ASSO_USE_SANDBOX_ALL est activé ou si c'est un mode test, utiliser les configurations sandbox
        if ($useSandboxAll || $isTestMode) {
            $useSandbox = true;
            $helloAssoClientId = env('HELLOASSO_SANDBOX_CLIENT_ID', env('HELLOASSO_CLIENT_ID', ''));
            $helloAssoClientSecret = env('HELLOASSO_SANDBOX_CLIENT_SECRET', env('HELLOASSO_CLIENT_SECRET', ''));
            $helloAssoOrgSlug = env('HELLOASSO_SANDBOX_ORG_SLUG', env('HELLOASSO_ORG_SLUG', ''));
        } else {
            $useSandbox = false;
            $helloAssoClientId = env('HELLOASSO_CLIENT_ID', '');
            $helloAssoClientSecret = env('HELLOASSO_CLIENT_SECRET', '');
            $helloAssoOrgSlug = env('HELLOASSO_ORG_SLUG', '');
        }
        
        // Log pour debug
        \Log::info('HelloAsso Configuration Check:', [
            'isTestMode' => $isTestMode,
            'useSandboxAll' => $useSandboxAll,
            'useSandbox' => $useSandbox,
            'hasClientId' => !empty($helloAssoClientId),
            'hasClientSecret' => !empty($helloAssoClientSecret),
            'hasOrgSlug' => !empty($helloAssoOrgSlug),
        ]);
        
        if ($helloAssoClientId && $helloAssoClientSecret && $helloAssoOrgSlug) {
            // Créer le checkout via l'API HelloAsso
            $helloAssoCheckoutUrl = $this->createHelloAssoCheckout($pilote, $montantTotal, $helloAssoClientId, $helloAssoClientSecret, $helloAssoOrgSlug, $competition, $useSandbox);
            
            if (!$helloAssoCheckoutUrl) {
                \Log::warning('HelloAsso checkout URL n\'a pas pu être généré');
                $helloAssoError = 'Impossible de générer le lien de paiement HelloAsso. ' . ($isTestMode && $useSandbox ? 'Vérifiez que vous utilisez les identifiants sandbox.' : '');
            }
        } else {
            \Log::warning('HelloAsso: Variables d\'environnement manquantes', [
                'isTestMode' => $isTestMode,
                'HELLOASSO_CLIENT_ID' => empty($helloAssoClientId) ? 'manquant' : 'présent',
                'HELLOASSO_CLIENT_SECRET' => empty($helloAssoClientSecret) ? 'manquant' : 'présent',
                'HELLOASSO_ORG_SLUG' => empty($helloAssoOrgSlug) ? 'manquant' : 'présent',
            ]);
        }

        return view('paiement', [
            'pilote' => $pilote,
            'competition' => $competition,
            'nombrePlaneurs' => $nombrePlaneurs,
            'montantPlaneur' => $montantPlaneur,
            'montantAdhesion' => $montantAdhesion,
            'montantTotal' => $montantTotal,
            'adresseCheque' => $adresseCheque,
            'ibanVirement' => $ibanVirement,
            'bicVirement' => $bicVirement,
            'referenceVirement' => $referenceVirement,
            'helloAssoCheckoutUrl' => $helloAssoCheckoutUrl,
            'helloAssoError' => $helloAssoError,
            'isTestMode' => $isTestMode,
            'useSandbox' => $useSandbox,
            'useSandboxAll' => $useSandboxAll,
        ]);
    }

    /**
     * Obtient un token d'accès HelloAsso via OAuth
     * @param string $clientId
     * @param string $clientSecret
     * @param bool $useSandbox Utiliser l'environnement sandbox (pour les tests)
     */
    private function getHelloAssoAccessToken($clientId, $clientSecret, $useSandbox = false)
    {
        try {
            if ($useSandbox) {
                // Utiliser l'environnement sandbox pour les tests
                $tokenUrl = env('HELLOASSO_TOKEN_URL_SANDBOX', 'https://api.helloasso-sandbox.com/oauth2/token');
                \Log::info('HelloAsso: Utilisation de l\'environnement SANDBOX pour les tests');
            } else {
                $tokenUrl = env('HELLOASSO_TOKEN_URL', 'https://api.helloasso.com/oauth2/token');
            }
            
            $response = Http::asForm()->post($tokenUrl, [
                'grant_type' => 'client_credentials',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['access_token'] ?? null;
            } else {
                \Log::error('Erreur lors de l\'obtention du token HelloAsso: ' . $response->body());
            }
        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'obtention du token HelloAsso: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Crée un checkout HelloAsso via l'API
     * @param object $pilote
     * @param float $montantTotal
     * @param string $clientId
     * @param string $clientSecret
     * @param string $orgSlug
     * @param object $competition
     * @param bool $useSandbox Utiliser l'environnement sandbox (pour les tests)
     */
    private function createHelloAssoCheckout($pilote, $montantTotal, $clientId, $clientSecret, $orgSlug, $competition, $useSandbox = false)
    {
        try {
            // Obtenir le token d'accès
            $accessToken = $this->getHelloAssoAccessToken($clientId, $clientSecret, $useSandbox);
            
            if (!$accessToken) {
                \Log::error('Impossible d\'obtenir le token d\'accès HelloAsso');
                return null;
            }

            if ($useSandbox) {
                // Utiliser l'environnement sandbox pour les tests
                $baseUrl = env('HELLOASSO_API_BASE_URL_SANDBOX', 'https://api.helloasso-sandbox.com/v5');
                \Log::info('HelloAsso: Utilisation de l\'API SANDBOX pour créer le checkout');
            } else {
                $baseUrl = env('HELLOASSO_API_BASE_URL', 'https://api.helloasso.com/v5');
            }
            
            // Générer les URLs absolues - utiliser APP_URL ou une URL de base configurée
            $appBaseUrl = env('APP_URL', config('app.url', 'http://localhost'));
            
            // Pour HelloAsso, on ne peut pas utiliser localhost, donc utiliser une URL configurée
            $helloAssoBaseUrl = env('HELLOASSO_REDIRECT_BASE_URL', $appBaseUrl);
            
            // Construire les URLs manuellement pour éviter localhost
            // backUrl : retour si l'utilisateur annule avant de payer
            $backUrl = rtrim($helloAssoBaseUrl, '/') . '/paiement/' . $pilote->identifiant_virement;
            // errorUrl : retour en cas d'erreur technique pendant le paiement
            $errorUrl = rtrim($helloAssoBaseUrl, '/') . '/paiement/' . $pilote->identifiant_virement . '/erreur';
            // returnUrl : retour après paiement réussi - rediriger vers la page de validation
            $returnUrl = rtrim($helloAssoBaseUrl, '/') . '/paiement/' . $pilote->identifiant_virement . '/validation';
            
            // S'assurer que les URLs sont en HTTPS (HelloAsso exige HTTPS)
            $backUrl = str_replace('http://', 'https://', $backUrl);
            $errorUrl = str_replace('http://', 'https://', $errorUrl);
            $returnUrl = str_replace('http://', 'https://', $returnUrl);
            
            // Préparer les données de la requête
            // Ajouter un timestamp pour rendre chaque checkout unique et éviter les conflits 409
            $uniqueId = $pilote->identifiant_virement . '-' . time();
            
            // Validation des données du payeur pour HelloAsso selon la documentation
            // https://dev.helloasso.com/docs/intégrer-le-paiement-sur-votre-site
            $firstName = trim($pilote->prenom ?? '');
            $lastName = trim($pilote->nom ?? '');
            $email = trim($pilote->email ?? '');
            
            // Validation selon les règles HelloAsso pour nom/prénom
            $validateName = function($name, $fieldName) {
                if (empty($name) || strlen($name) < 2) {
                    return "Le champ {$fieldName} est trop court ou vide";
                }
                
                // Liste des valeurs interdites selon la documentation HelloAsso
                $forbiddenValues = [
                    'firstname', 'lastname', 'unknown', 'first_name', 'last_name',
                    'anonyme', 'user', 'admin', 'name', 'nom', 'prénom', 'test'
                ];
                
                if (in_array(strtolower($name), $forbiddenValues)) {
                    return "Le champ {$fieldName} contient une valeur interdite: {$name}";
                }
                
                // Ne doit pas contenir 3 caractères répétitifs
                if (preg_match('/(.)\1{2,}/', $name)) {
                    return "Le champ {$fieldName} contient 3 caractères répétitifs";
                }
                
                // Ne doit pas contenir de chiffres
                if (preg_match('/\d/', $name)) {
                    return "Le champ {$fieldName} contient des chiffres";
                }
                
                // Ne doit pas être un seul caractère (déjà vérifié plus haut mais double vérification)
                if (strlen($name) == 1) {
                    return "Le champ {$fieldName} ne doit pas être un seul caractère";
                }
                
                // Ne doit pas ne contenir aucune voyelle
                if (!preg_match('/[aeiouyéèêëàâäùûüôöîï]/i', $name)) {
                    return "Le champ {$fieldName} ne contient aucune voyelle";
                }
                
                // Caractères autorisés : lettres de l'alphabet latin, é, à, ù, ', -, ç
                // Supprimer les caractères spéciaux autorisés avant de vérifier
                $cleaned = preg_replace("/['\-çéàùÉÀÙÇ]/u", '', $name);
                if (!preg_match('/^[a-zA-Z\s]+$/u', $cleaned)) {
                    return "Le champ {$fieldName} contient des caractères non autorisés (seules les lettres de l'alphabet latin, é, à, ù, ', -, ç sont autorisées)";
                }
                
                return null; // Validation OK
            };
            
            // Valider firstName
            $firstNameError = $validateName($firstName, 'firstName');
            if ($firstNameError) {
                \Log::error('HelloAsso: ' . $firstNameError, ['firstName' => $firstName]);
                return null;
            }
            
            // Valider lastName
            $lastNameError = $validateName($lastName, 'lastName');
            if ($lastNameError) {
                \Log::error('HelloAsso: ' . $lastNameError, ['lastName' => $lastName]);
                return null;
            }
            
            // Le nom et le prénom ne doivent pas être identiques
            if (strtolower($firstName) === strtolower($lastName)) {
                \Log::error('HelloAsso: firstName et lastName ne doivent pas être identiques', [
                    'firstName' => $firstName,
                    'lastName' => $lastName
                ]);
                return null;
            }
            
            // Valider l'email
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                \Log::error('HelloAsso: email invalide', ['email' => $email]);
                return null;
            }
            
            // Construire le nom de l'item : éviter de dupliquer l'année si elle est déjà dans le nom
            $competitionName = $competition->nom ?? 'Wassmer Cup';
            $itemName = 'Inscription ' . $competitionName;
            // Ajouter 2026 seulement si ce n'est pas déjà dans le nom de la compétition
            if (!str_contains($competitionName, '2026')) {
                $itemName .= ' 2026';
            }
            
            $requestData = [
                'totalAmount' => (int)($montantTotal * 100), // Montant en centimes (doit être un entier)
                'initialAmount' => (int)($montantTotal * 100),
                'containsDonation' => false, // Champ requis par l'API
                'itemName' => $itemName,
                'backUrl' => $backUrl,
                'errorUrl' => $errorUrl,
                'returnUrl' => $returnUrl,
                'payer' => [
                    'firstName' => $firstName,
                    'lastName' => $lastName,
                    'email' => $email,
                ],
                // Les métadonnées peuvent causer des conflits, essayons sans ou avec un format différent
                // 'metadata' => [
                //     'pilote_id' => (string) $pilote->id,
                //     'identifiant_virement' => $pilote->identifiant_virement,
                //     'unique_id' => $uniqueId,
                // ],
            ];
            
            // Logger les données complètes pour debug
            \Log::info('HelloAsso Checkout Request:', [
                'url' => "{$baseUrl}/organizations/{$orgSlug}/checkout-intents",
                'backUrl' => $backUrl,
                'errorUrl' => $errorUrl,
                'returnUrl' => $returnUrl,
                'request_data' => $requestData,
            ]);
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post("{$baseUrl}/organizations/{$orgSlug}/checkout-intents", $requestData);

            if ($response->successful()) {
                $data = $response->json();
                \Log::info('Réponse HelloAsso checkout:', $data);
                
                // Récupérer le checkoutIntentId depuis la réponse
                // La réponse peut avoir différentes structures selon l'API HelloAsso
                $checkoutIntentId = null;
                if (isset($data['id']) && !empty($data['id'])) {
                    $checkoutIntentId = trim($data['id']);
                } elseif (isset($data['checkoutIntentId']) && !empty($data['checkoutIntentId'])) {
                    $checkoutIntentId = trim($data['checkoutIntentId']);
                } elseif (isset($data['checkout_intent_id']) && !empty($data['checkout_intent_id'])) {
                    $checkoutIntentId = trim($data['checkout_intent_id']);
                } elseif (isset($data['checkout_intent']) && is_array($data['checkout_intent']) && isset($data['checkout_intent']['id']) && !empty($data['checkout_intent']['id'])) {
                    $checkoutIntentId = trim($data['checkout_intent']['id']);
                }
                
                // Log pour déboguer
                \Log::info('Extraction checkoutIntentId:', [
                    'checkoutIntentId' => $checkoutIntentId,
                    'pilote_type' => get_class($pilote),
                    'pilote_id' => $pilote->id ?? 'N/A',
                    'is_pilote_instance' => $pilote instanceof Pilote,
                ]);
                
                // Stocker le checkoutIntentId dans la base de données si c'est un objet Pilote (modèle Eloquent)
                if (!empty($checkoutIntentId) && $pilote instanceof Pilote && isset($pilote->id)) {
                    try {
                        // Recharger le pilote depuis la base pour éviter les problèmes de cache
                        $piloteToUpdate = Pilote::find($pilote->id);
                        if ($piloteToUpdate) {
                            $piloteToUpdate->helloasso_checkout_intent_id = $checkoutIntentId;
                            $piloteToUpdate->save();
                            \Log::info('CheckoutIntentId stocké pour le pilote', [
                                'pilote_id' => $piloteToUpdate->id,
                                'checkoutIntentId' => $checkoutIntentId,
                                'verification' => $piloteToUpdate->fresh()->helloasso_checkout_intent_id
                            ]);
                        } else {
                            \Log::warning('Pilote non trouvé pour sauvegarder checkoutIntentId', [
                                'pilote_id' => $pilote->id
                            ]);
                        }
                    } catch (\Exception $e) {
                        \Log::error('Impossible de stocker le checkoutIntentId: ' . $e->getMessage(), [
                            'pilote_id' => $pilote->id,
                            'checkoutIntentId' => $checkoutIntentId,
                            'trace' => $e->getTraceAsString()
                        ]);
                    }
                } elseif (!empty($checkoutIntentId) && is_object($pilote) && isset($pilote->id)) {
                    // En mode test, on ne peut pas sauvegarder car c'est un stdClass
                    \Log::info('CheckoutIntentId généré en mode test (non sauvegardé)', [
                        'pilote_id' => $pilote->id,
                        'checkoutIntentId' => $checkoutIntentId
                    ]);
                } else {
                    \Log::warning('CheckoutIntentId non sauvegardé', [
                        'checkoutIntentId' => $checkoutIntentId,
                        'pilote_type' => get_class($pilote),
                        'pilote_id' => $pilote->id ?? 'N/A',
                        'is_pilote_instance' => $pilote instanceof Pilote,
                    ]);
                }
                
                // La réponse peut contenir redirectUrl directement ou dans un objet
                $redirectUrl = null;
                if (isset($data['redirectUrl'])) {
                    $redirectUrl = $data['redirectUrl'];
                } elseif (isset($data['redirect_url'])) {
                    $redirectUrl = $data['redirect_url'];
                } elseif (isset($data['url'])) {
                    $redirectUrl = $data['url'];
                } elseif (isset($data['checkoutUrl'])) {
                    $redirectUrl = $data['checkoutUrl'];
                } elseif (isset($data['checkout_url'])) {
                    $redirectUrl = $data['checkout_url'];
                } elseif (isset($data['checkout_intent']) && isset($data['checkout_intent']['redirectUrl'])) {
                    $redirectUrl = $data['checkout_intent']['redirectUrl'];
                }
                
                if ($redirectUrl) {
                    // Retourner l'URL de redirection (compatibilité avec le code existant)
                    return $redirectUrl;
                } else {
                    \Log::error('Format de réponse HelloAsso inattendu:', $data);
                    \Log::error('Structure complète de la réponse:', json_encode($data, JSON_PRETTY_PRINT));
                }
            } else {
                $errorBody = $response->body();
                $errorJson = $response->json();
                $statusCode = $response->status();
                
                \Log::error('Erreur lors de la création du checkout HelloAsso', [
                    'status' => $statusCode,
                    'body' => $errorBody,
                    'json' => $errorJson,
                    'headers' => $response->headers(),
                ]);
                
                // En cas d'erreur 409 (conflit), cela peut être dû à un checkout déjà existant
                if ($statusCode === 409) {
                    \Log::warning('Conflit 409 détecté - un checkout avec ces paramètres existe peut-être déjà');
                }
            }
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la création du checkout HelloAsso: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Affiche la page de validation après un paiement HelloAsso réussi
     */
    public function validation(Request $request, $identifiantVirement)
    {
        // Récupérer les paramètres de retour HelloAsso
        $checkoutIntentId = $request->query('checkoutIntentId');
        $code = $request->query('code'); // devrait être "succeeded"
        $orderId = $request->query('orderId');
        
        // Rechercher le pilote par identifiant_virement ou créer un pilote fictif si c'est un test
        $pilote = null;
        $isTestMode = false;
        
        // Vérifier si c'est un identifiant de test
        if (str_starts_with($identifiantVirement, 'TEST-')) {
            $competition = Competition::active();
            $pilote = (object)[
                'id' => 999999,
                'nom' => 'Dupont',
                'prenom' => 'Jean',
                'email' => 'test@example.com',
                'identifiant_virement' => $identifiantVirement,
            ];
            $isTestMode = true;
        } else {
            $pilote = Pilote::with(['competition', 'planeurs'])
                ->where('identifiant_virement', $identifiantVirement)
                ->first();
        }

        if (!$pilote) {
            return redirect('/')->with('error', 'Pilote non trouvé ou identifiant invalide.');
        }

        $competition = (!$isTestMode && isset($pilote->competition)) ? $pilote->competition : Competition::active();
        
        // Log les paramètres reçus pour debug
        \Log::info('HelloAsso Payment Validation:', [
            'identifiantVirement' => $identifiantVirement,
            'checkoutIntentId' => $checkoutIntentId,
            'code' => $code,
            'orderId' => $orderId,
            'pilote_id' => $pilote->id,
        ]);

        // Vérifier si le paiement a réussi
        $paiementReussi = ($code === 'succeeded' && $checkoutIntentId);
        
        if ($paiementReussi) {
            // IMPORTANT: Ne pas valider automatiquement le paiement basé uniquement sur le returnUrl
            // La validation définitive doit se faire via les webhooks/notifications HelloAsso
            // Ici, on affiche juste une confirmation à l'utilisateur
            
            // Optionnellement, on peut marquer le paiement comme "en attente de confirmation"
            // si ce n'est pas déjà fait (attendre la notification HelloAsso pour valider définitivement)
        }

        return view('paiement-validation', [
            'pilote' => $pilote,
            'competition' => $competition,
            'checkoutIntentId' => $checkoutIntentId,
            'orderId' => $orderId,
            'paiementReussi' => $paiementReussi,
        ]);
    }

    /**
     * Redirige vers la page de paiement de test avec un identifiant fixe
     * Accessible uniquement aux administrateurs pour tester HelloAsso
     */
    public function test()
    {
        // Vérifier que l'utilisateur est authentifié en tant qu'administrateur
        if (!Auth::guard('web')->check()) {
            abort(403, 'Accès non autorisé. Vous devez être connecté en tant qu\'administrateur.');
        }

        $user = Auth::guard('web')->user();
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Accès non autorisé. Cette page est réservée aux administrateurs.');
        }

        // Rediriger vers la page de paiement avec l'identifiant fixe de test
        // La méthode index() détectera automatiquement que c'est un identifiant de test
        return redirect()->route('paiement.public', ['identifiantVirement' => 'TEST-00000']);

        $nombrePlaneurs = 0;
        $montantPlaneur = 50;
        $montantAdhesion = 50;
        $montantTotal = 1.00; // Montant fixe de 1€ pour les tests

        // Informations de paiement depuis la base de données (ou .env en fallback)
        $config = PaiementConfiguration::getConfiguration();
        $adresseCheque = $config->adresse_cheque ?: env('PAIEMENT_CHEQUE_ADRESSE', '');
        $ibanVirement = $config->iban_virement ?: env('PAIEMENT_VIREMENT_IBAN', '');
        $bicVirement = $config->bic_virement ?: env('PAIEMENT_VIREMENT_BIC', '');
        $referenceVirement = $piloteFictif->identifiant_virement;
        
        // Générer l'URL du checkout HelloAsso via l'API
        $helloAssoCheckoutUrl = null;
        $helloAssoError = null;
        
        // Vérifier si HELLO_ASSO_USE_SANDBOX_ALL est activé ou utiliser HELLOASSO_USE_SANDBOX
        $useSandboxAll = env('HELLO_ASSO_USE_SANDBOX_ALL', false);
        $useSandbox = $useSandboxAll || env('HELLOASSO_USE_SANDBOX', true); // Par défaut, utiliser sandbox pour la page de test
        
        // Utiliser les identifiants sandbox ou production selon le mode
        if ($useSandbox) {
            $helloAssoClientId = env('HELLOASSO_SANDBOX_CLIENT_ID', env('HELLOASSO_CLIENT_ID', ''));
            $helloAssoClientSecret = env('HELLOASSO_SANDBOX_CLIENT_SECRET', env('HELLOASSO_CLIENT_SECRET', ''));
            $helloAssoOrgSlug = env('HELLOASSO_SANDBOX_ORG_SLUG', env('HELLOASSO_ORG_SLUG', ''));
        } else {
            $helloAssoClientId = env('HELLOASSO_CLIENT_ID', '');
            $helloAssoClientSecret = env('HELLOASSO_CLIENT_SECRET', '');
            $helloAssoOrgSlug = env('HELLOASSO_ORG_SLUG', '');
        }
        
        // Log pour debug
        \Log::info('HelloAsso Test Configuration Check:', [
            'useSandbox' => $useSandbox,
            'hasClientId' => !empty($helloAssoClientId),
            'hasClientSecret' => !empty($helloAssoClientSecret),
            'hasOrgSlug' => !empty($helloAssoOrgSlug),
        ]);
        
        if ($helloAssoClientId && $helloAssoClientSecret && $helloAssoOrgSlug) {
            // Créer le checkout via l'API HelloAsso avec le montant de test (1€)
            // Utiliser l'environnement SANDBOX pour les tests (par défaut)
            $helloAssoCheckoutUrl = $this->createHelloAssoCheckout($piloteFictif, $montantTotal, $helloAssoClientId, $helloAssoClientSecret, $helloAssoOrgSlug, $competition, $useSandbox);
            
            if (!$helloAssoCheckoutUrl) {
                \Log::warning('HelloAsso test checkout URL n\'a pas pu être généré');
                $helloAssoError = 'Impossible de générer le lien de paiement HelloAsso. ' . ($useSandbox ? 'Vérifiez que vous utilisez les identifiants sandbox (HELLOASSO_CLIENT_ID et HELLOASSO_CLIENT_SECRET du sandbox).' : '');
            }
        } else {
            \Log::warning('HelloAsso Test: Variables d\'environnement manquantes', [
                'HELLOASSO_CLIENT_ID' => empty($helloAssoClientId) ? 'manquant' : 'présent',
                'HELLOASSO_CLIENT_SECRET' => empty($helloAssoClientSecret) ? 'manquant' : 'présent',
                'HELLOASSO_ORG_SLUG' => empty($helloAssoOrgSlug) ? 'manquant' : 'présent',
            ]);
        }

        return view('paiement', [
            'pilote' => $piloteFictif,
            'competition' => $competition,
            'nombrePlaneurs' => $nombrePlaneurs,
            'montantPlaneur' => $montantPlaneur,
            'montantAdhesion' => $montantAdhesion,
            'montantTotal' => $montantTotal,
            'adresseCheque' => $adresseCheque,
            'ibanVirement' => $ibanVirement,
            'bicVirement' => $bicVirement,
            'referenceVirement' => $referenceVirement,
            'helloAssoCheckoutUrl' => $helloAssoCheckoutUrl,
            'helloAssoError' => $helloAssoError,
            'isTestMode' => true, // Flag pour indiquer que c'est un mode test
            'useSandbox' => $useSandbox, // Indiquer si le sandbox est utilisé
            'useSandboxAll' => $useSandboxAll, // Indiquer si USE_SANDBOX_ALL est activé
        ]);
    }

    /**
     * Vérifie le statut d'un paiement HelloAsso via polling
     * Selon la documentation : https://dev.helloasso.com/docs/validation-de-vos-paiements
     * 
     * @param Request $request
     * @param string|null $checkoutIntentId L'identifiant du checkout HelloAsso (optionnel, peut être passé en paramètre)
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkPaiement(Request $request, $checkoutIntentId = null)
    {
        // Récupérer le checkoutIntentId depuis la requête ou le paramètre
        $checkoutIntentId = $checkoutIntentId ?? $request->input('checkoutIntentId');
        
        if (!$checkoutIntentId) {
            return response()->json([
                'success' => false,
                'error' => 'checkoutIntentId est requis'
            ], 400);
        }

        // Vérifier si HELLO_ASSO_USE_SANDBOX_ALL est activé (force le mode sandbox pour tous les paiements)
        $useSandboxAll = env('HELLO_ASSO_USE_SANDBOX_ALL', false);
        $useSandbox = $useSandboxAll || env('HELLOASSO_USE_SANDBOX', false);
        
        // Obtenir les identifiants selon le mode
        if ($useSandbox) {
            $helloAssoClientId = env('HELLOASSO_SANDBOX_CLIENT_ID', env('HELLOASSO_CLIENT_ID', ''));
            $helloAssoClientSecret = env('HELLOASSO_SANDBOX_CLIENT_SECRET', env('HELLOASSO_CLIENT_SECRET', ''));
            $helloAssoOrgSlug = env('HELLOASSO_SANDBOX_ORG_SLUG', env('HELLOASSO_ORG_SLUG', ''));
        } else {
            $helloAssoClientId = env('HELLOASSO_CLIENT_ID', '');
            $helloAssoClientSecret = env('HELLOASSO_CLIENT_SECRET', '');
            $helloAssoOrgSlug = env('HELLOASSO_ORG_SLUG', '');
        }

        if (!$helloAssoClientId || !$helloAssoClientSecret || !$helloAssoOrgSlug) {
            return response()->json([
                'success' => false,
                'error' => 'Configuration HelloAsso manquante'
            ], 500);
        }

        try {
            // Obtenir le token d'accès
            $accessToken = $this->getHelloAssoAccessToken($helloAssoClientId, $helloAssoClientSecret, $useSandbox);
            
            if (!$accessToken) {
                \Log::error('Impossible d\'obtenir le token d\'accès HelloAsso pour checkPaiement');
                return response()->json([
                    'success' => false,
                    'error' => 'Impossible d\'obtenir le token d\'accès HelloAsso'
                ], 500);
            }

            // Déterminer l'URL de base selon le mode
            if ($useSandbox) {
                $baseUrl = env('HELLOASSO_API_BASE_URL_SANDBOX', 'https://api.helloasso-sandbox.com/v5');
            } else {
                $baseUrl = env('HELLOASSO_API_BASE_URL', 'https://api.helloasso.com/v5');
            }

            // Faire un GET pour récupérer le statut du checkout
            // Documentation : https://api.helloasso.com/v5/organizations/{asso-slug}/checkout-intents/{checkoutIntentId}
            $url = "{$baseUrl}/organizations/{$helloAssoOrgSlug}/checkout-intents/{$checkoutIntentId}";
            
            \Log::info('HelloAsso Checkout Status Request:', [
                'url' => $url,
                'checkoutIntentId' => $checkoutIntentId,
                'useSandbox' => $useSandbox,
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->get($url);

            if ($response->successful()) {
                $data = $response->json();
                \Log::info('HelloAsso Checkout Status Response:', $data);

                // Vérifier si une commande (order) existe
                // Si order existe, cela signifie que le paiement a réussi
                $hasOrder = isset($data['order']) && !empty($data['order']);
                $orderId = $hasOrder ? ($data['order']['id'] ?? null) : null;
                $metadata = $data['metadata'] ?? null;

                // Trouver le pilote par checkoutIntentId (sans mise à jour automatique)
                $pilote = null;
                $piloteId = null;
                
                if ($hasOrder) {
                    // Chercher le pilote par checkoutIntentId
                    $pilote = Pilote::where('helloasso_checkout_intent_id', $checkoutIntentId)->first();
                    
                    if ($pilote) {
                        $piloteId = $pilote->id;
                    } else {
                        \Log::warning("Pilote non trouvé pour checkoutIntentId: {$checkoutIntentId}");
                    }
                }

                return response()->json([
                    'success' => true,
                    'checkoutIntentId' => $checkoutIntentId,
                    'hasOrder' => $hasOrder,
                    'orderId' => $orderId,
                    'metadata' => $metadata,
                    'piloteId' => $piloteId,
                    'paiementValide' => $hasOrder, // Le paiement est valide si une commande existe
                    'checkoutData' => $data,
                ]);
            } else {
                $errorBody = $response->body();
                $statusCode = $response->status();
                
                \Log::error('Erreur lors de la vérification du statut HelloAsso', [
                    'status' => $statusCode,
                    'body' => $errorBody,
                    'checkoutIntentId' => $checkoutIntentId,
                ]);

                return response()->json([
                    'success' => false,
                    'error' => 'Erreur lors de la vérification du statut',
                    'status' => $statusCode,
                    'details' => $errorBody,
                ], $statusCode);
            }
        } catch (\Exception $e) {
            \Log::error('Exception lors de la vérification du statut HelloAsso: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la vérification du statut: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vérifie tous les paiements en attente via polling HelloAsso
     * Cette méthode boucle sur tous les pilotes avec paiement_valide = false
     * et vérifie leur statut auprès de HelloAsso
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifierTousLesPaiements()
    {
        // Vérifier que l'utilisateur est authentifié en tant qu'administrateur
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            abort(403, 'Accès non autorisé. Cette page est réservée aux administrateurs.');
        }

        // Récupérer tous les pilotes avec paiement non validé et ayant un checkoutIntentId
        $pilotesEnAttente = Pilote::where('paiement_valide', false)
            ->whereNotNull('helloasso_checkout_intent_id')
            ->get();

        $resultats = [
            'total' => $pilotesEnAttente->count(),
            'valides' => 0,
            'en_attente' => 0,
            'erreurs' => 0,
            'details' => []
        ];

        \Log::info("Début de la vérification de {$resultats['total']} paiements en attente");

        foreach ($pilotesEnAttente as $pilote) {
            try {
                // Appeler checkPaiement directement avec le checkoutIntentId
                $request = new Request();
                $response = $this->checkPaiement($request, $pilote->helloasso_checkout_intent_id);
                $responseData = json_decode($response->getContent(), true);

                if ($responseData && isset($responseData['success']) && $responseData['success']) {
                    if ($responseData['paiementValide'] ?? false) {
                        // Le paiement est validé, mettre à jour le pilote
                        $pilote->paiement_valide = true;
                        $pilote->save();
                        
                        $resultats['valides']++;
                        $resultats['details'][] = [
                            'pilote_id' => $pilote->id,
                            'identifiant_virement' => $pilote->identifiant_virement,
                            'nom' => $pilote->nom . ' ' . $pilote->prenom,
                            'statut' => 'validé',
                            'checkoutIntentId' => $pilote->helloasso_checkout_intent_id,
                            'orderId' => $responseData['orderId'] ?? null,
                        ];
                        
                        \Log::info("Paiement validé pour le pilote {$pilote->id} ({$pilote->identifiant_virement})", [
                            'checkoutIntentId' => $pilote->helloasso_checkout_intent_id,
                            'orderId' => $responseData['orderId'] ?? null,
                        ]);
                    } else {
                        // Le paiement est toujours en attente
                        $resultats['en_attente']++;
                        $resultats['details'][] = [
                            'pilote_id' => $pilote->id,
                            'identifiant_virement' => $pilote->identifiant_virement,
                            'nom' => $pilote->nom . ' ' . $pilote->prenom,
                            'statut' => 'en_attente',
                            'checkoutIntentId' => $pilote->helloasso_checkout_intent_id,
                        ];
                    }
                } else {
                    // Erreur lors de la vérification
                    $resultats['erreurs']++;
                    $errorMessage = $responseData['error'] ?? 'Erreur inconnue';
                    $resultats['details'][] = [
                        'pilote_id' => $pilote->id,
                        'identifiant_virement' => $pilote->identifiant_virement,
                        'nom' => $pilote->nom . ' ' . $pilote->prenom,
                        'statut' => 'erreur',
                        'checkoutIntentId' => $pilote->helloasso_checkout_intent_id,
                        'erreur' => $errorMessage,
                    ];
                    
                    \Log::warning("Erreur lors de la vérification du paiement pour le pilote {$pilote->id}", [
                        'checkoutIntentId' => $pilote->helloasso_checkout_intent_id,
                        'erreur' => $errorMessage,
                    ]);
                }
            } catch (\Exception $e) {
                $resultats['erreurs']++;
                $resultats['details'][] = [
                    'pilote_id' => $pilote->id,
                    'identifiant_virement' => $pilote->identifiant_virement,
                    'nom' => $pilote->nom . ' ' . $pilote->prenom,
                    'statut' => 'exception',
                    'checkoutIntentId' => $pilote->helloasso_checkout_intent_id,
                    'erreur' => $e->getMessage(),
                ];
                
                \Log::error("Exception lors de la vérification du paiement pour le pilote {$pilote->id}: " . $e->getMessage());
            }
        }

        \Log::info("Fin de la vérification des paiements", $resultats);

        return response()->json([
            'success' => true,
            'message' => "Vérification terminée : {$resultats['valides']} paiement(s) validé(s), {$resultats['en_attente']} en attente, {$resultats['erreurs']} erreur(s)",
            'resultats' => $resultats,
        ]);
    }
}

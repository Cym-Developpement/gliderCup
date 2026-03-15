<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mon inscription - Wassmer Cup</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-sky-50 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Mon inscription</h1>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-blue-600 hover:text-blue-800">
                        Déconnexion
                    </button>
                </form>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                    <p class="font-semibold">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                    <p class="font-semibold">{{ session('error') }}</p>
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                    <p class="font-bold mb-2">Erreurs lors de l'enregistrement :</p>
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Statut de l'inscription -->
            <div class="mb-6">
                @if($pilote->statut === 'en_attente')
                    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded">
                        <p class="text-yellow-700 font-semibold">
                            ⏳ Votre inscription est en attente de validation par un administrateur.
                        </p>
                    </div>
                @elseif($pilote->statut === 'validee')
                    <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded">
                        <p class="text-green-700 font-semibold">
                            ✅ Votre inscription a été validée !
                        </p>
                    </div>
                @elseif($pilote->statut === 'refusee')
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded">
                        <p class="text-red-700 font-semibold">
                            ❌ Votre inscription a été refusée.
                        </p>
                    </div>
                @endif
            </div>

            <!-- Statut du paiement -->
            @if(!$pilote->paiement_valide)
            <div class="mb-6">
                <div class="bg-orange-50 border-l-4 border-orange-500 p-4 rounded">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-orange-700 font-semibold mb-1">
                                💳 Paiement en attente
                            </p>
                            <p class="text-orange-600 text-sm">
                                Votre paiement n'a pas encore été validé. Veuillez procéder au paiement pour finaliser votre inscription.
                            </p>
                        </div>
                        <a href="{{ route('paiement.index') }}" class="bg-orange-600 hover:bg-orange-700 text-white font-bold py-2 px-6 rounded-lg transition duration-200 shadow-lg ml-4 whitespace-nowrap">
                            Accéder au paiement
                        </a>
                    </div>
                </div>
            </div>
            @else
            <div class="mb-6">
                <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded">
                    <p class="text-green-700 font-semibold">
                        ✅ Votre paiement a été validé.
                    </p>
                </div>
            </div>
            @endif

            <!-- Informations du pilote -->
            <div class="mb-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Informations du pilote</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Nom</p>
                        <p class="font-semibold">{{ $pilote->nom }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Prénom</p>
                        <p class="font-semibold">{{ $pilote->prenom }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Qualité</p>
                        <p class="font-semibold">{{ $pilote->qualite }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Date de naissance</p>
                        <p class="font-semibold">{{ $pilote->date_naissance->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">E-mail</p>
                        <p class="font-semibold">{{ $pilote->email }}</p>
                    </div>
                    @if($pilote->telephone)
                        <div>
                            <p class="text-sm text-gray-600">Téléphone</p>
                            <p class="font-semibold">{{ $pilote->telephone }}</p>
                        </div>
                    @endif
                    @if($pilote->numero_ffvp)
                        <div>
                            <p class="text-sm text-gray-600">N° FFVP</p>
                            <p class="font-semibold">{{ $pilote->numero_ffvp }}</p>
                        </div>
                    @endif
                    @if($pilote->club)
                        <div>
                            <p class="text-sm text-gray-600">Club</p>
                            <p class="font-semibold">{{ $pilote->club }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Informations des planeurs -->
            @if($planeurs->count() > 0)
                <div class="mb-6">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Planeurs inscrits</h2>
                    @foreach($planeurs as $planeur)
                        <div class="bg-gray-50 p-4 rounded-lg mb-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                @if($planeur->marque)
                                    <div>
                                        <p class="text-sm text-gray-600">Marque</p>
                                        <p class="font-semibold">{{ $planeur->marque }}</p>
                                    </div>
                                @endif
                                <div>
                                    <p class="text-sm text-gray-600">Modèle</p>
                                    <p class="font-semibold">{{ $planeur->modele }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Immatriculation</p>
                                    <p class="font-semibold">{{ $planeur->immatriculation }}</p>
                                </div>
                                @if($planeur->type)
                                    <div>
                                        <p class="text-sm text-gray-600">Type</p>
                                        <p class="font-semibold">{{ ucfirst($planeur->type) }}</p>
                                    </div>
                                @endif
                            </div>

                            <!-- Documents du planeur -->
                            <div class="mt-4 pt-4 border-t border-gray-300">
                                <h3 class="text-lg font-semibold text-gray-800 mb-3">Documents du planeur</h3>
                                
                                @php
                                    $documentsPlaneur = [];
                                    if ($planeur->cdn_cen) {
                                        $documentsPlaneur[] = ['nom' => 'CDN / CEN', 'fichier' => $planeur->cdn_cen];
                                    }
                                    if ($planeur->responsabilite_civile) {
                                        $documentsPlaneur[] = ['nom' => 'Responsabilité civile', 'fichier' => $planeur->responsabilite_civile];
                                    }
                                @endphp

                                @if(count($documentsPlaneur) > 0)
                                    <div class="bg-green-50 border-l-4 border-green-500 p-3 rounded-lg mb-3">
                                        <h4 class="text-sm font-semibold text-gray-800 mb-2">📄 Documents téléchargés</h4>
                                        <div class="space-y-2">
                                            @foreach($documentsPlaneur as $doc)
                                                <div class="flex justify-between items-center bg-white p-2 rounded">
                                                    <span class="text-sm font-medium text-gray-700">{{ $doc['nom'] }}</span>
                                                    <a href="/storage/{{ $doc['fichier'] }}" target="_blank" download class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm font-medium flex items-center gap-2 transition">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                        </svg>
                                                        Télécharger
                                                    </a>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <form action="{{ route('dashboard.upload-documents-planeur', $planeur->id) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                                    @csrf
                                    
                                    <!-- CDN / CEN -->
                                    <div class="bg-white p-3 rounded border border-gray-200">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            CDN / CEN
                                            @if($planeur->cdn_cen)
                                                <span class="text-green-600 text-xs ml-2">✓ Déjà fourni</span>
                                            @else
                                                <span class="text-red-600 text-xs ml-2">⚠ Manquant</span>
                                            @endif
                                        </label>
                                        @if($planeur->cdn_cen)
                                            <div class="mb-2">
                                                <a href="/storage/{{ $planeur->cdn_cen }}" target="_blank" download class="text-blue-600 hover:text-blue-800 text-sm font-medium inline-flex items-center gap-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                    Télécharger le document actuel
                                                </a>
                                            </div>
                                        @endif
                                        <input type="file" name="cdn_cen" accept=".pdf,.jpg,.jpeg,.png" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <p class="text-xs text-gray-500 mt-1">Formats acceptés : PDF, JPG, JPEG, PNG (max 100 Mo)</p>
                                    </div>

                                    <!-- Responsabilité civile -->
                                    <div class="bg-white p-3 rounded border border-gray-200">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Responsabilité civile
                                            @if($planeur->responsabilite_civile)
                                                <span class="text-green-600 text-xs ml-2">✓ Déjà fourni</span>
                                            @else
                                                <span class="text-red-600 text-xs ml-2">⚠ Manquant</span>
                                            @endif
                                        </label>
                                        @if($planeur->responsabilite_civile)
                                            <div class="mb-2">
                                                <a href="/storage/{{ $planeur->responsabilite_civile }}" target="_blank" download class="text-blue-600 hover:text-blue-800 text-sm font-medium inline-flex items-center gap-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                    Télécharger le document actuel
                                                </a>
                                            </div>
                                        @endif
                                        <input type="file" name="responsabilite_civile" accept=".pdf,.jpg,.jpeg,.png" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <p class="text-xs text-gray-500 mt-1">Formats acceptés : PDF, JPG, JPEG, PNG (max 100 Mo)</p>
                                    </div>

                                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition duration-200">
                                        Enregistrer les documents du planeur
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="mb-6">
                    <p class="text-gray-600">Aucun planeur inscrit.</p>
                </div>
            @endif

            <!-- Documents du pilote -->
            <div class="mb-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Documents du pilote</h2>
                
                <!-- Section récapitulative des documents téléchargés -->
                @php
                    $age = \Carbon\Carbon::parse($pilote->date_naissance)->age;
                    $estMineur = $age < 18;
                    $documents = [];
                    if ($estMineur && $pilote->autorisation_parentale) {
                        $documents[] = ['nom' => 'Autorisation parentale', 'fichier' => $pilote->autorisation_parentale];
                    }
                    if ($pilote->feuille_declarative_qualifications) {
                        $documents[] = ['nom' => 'Feuille déclarative qualifications', 'fichier' => $pilote->feuille_declarative_qualifications];
                    }
                    if ($pilote->visite_medicale_classe_2) {
                        $documents[] = ['nom' => 'Visite médicale classe 2', 'fichier' => $pilote->visite_medicale_classe_2];
                    }
                    if ($pilote->spl_valide) {
                        $documents[] = ['nom' => 'SPL Valide', 'fichier' => $pilote->spl_valide];
                    }
                @endphp
                
                @if(count($documents) > 0)
                    <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg mb-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">📄 Documents téléchargés</h3>
                        <div class="space-y-2">
                            @foreach($documents as $doc)
                                <div class="flex justify-between items-center bg-white p-3 rounded">
                                    <span class="text-sm font-medium text-gray-700">{{ $doc['nom'] }}</span>
                                    <a href="/storage/{{ $doc['fichier'] }}" target="_blank" download class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium flex items-center gap-2 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Télécharger
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Formulaire pour ajouter/modifier les documents -->
                <div class="bg-gray-50 p-4 rounded-lg mb-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Ajouter ou modifier des documents</h3>
                    <form action="{{ route('dashboard.upload-documents') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        
                        <!-- Autorisation parentale (si mineur) -->
                        @if($estMineur)
                            <div class="bg-white p-4 rounded-lg border border-gray-200">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Autorisation parentale signée
                                    @if($pilote->autorisation_parentale)
                                        <span class="text-green-600 text-xs ml-2">✓ Déjà fourni</span>
                                    @else
                                        <span class="text-red-600 text-xs ml-2">⚠ Manquant</span>
                                    @endif
                                </label>
                                @if($pilote->autorisation_parentale)
                                    <div class="mb-2">
                                        <a href="/storage/{{ $pilote->autorisation_parentale }}" target="_blank" download class="text-blue-600 hover:text-blue-800 text-sm font-medium inline-flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            Télécharger le document actuel
                                        </a>
                                    </div>
                                @endif
                                <input type="file" name="autorisation_parentale" accept=".pdf,.jpg,.jpeg,.png" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <p class="text-xs text-gray-500 mt-1">Formats acceptés : PDF, JPG, JPEG, PNG (max 100 Mo)</p>
                            </div>
                        @endif

                        <!-- Feuille déclarative qualifications -->
                        <div class="bg-white p-4 rounded-lg border border-gray-200">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Feuille déclarative attestant que le pilote sera à jour de ses qualifications durant la rencontre
                                @if($pilote->feuille_declarative_qualifications)
                                    <span class="text-green-600 text-xs ml-2">✓ Déjà fourni</span>
                                @else
                                    <span class="text-red-600 text-xs ml-2">⚠ Manquant</span>
                                @endif
                            </label>
                            @if($pilote->feuille_declarative_qualifications)
                                <div class="mb-2">
                                    <a href="/storage/{{ $pilote->feuille_declarative_qualifications }}" target="_blank" download class="text-blue-600 hover:text-blue-800 text-sm font-medium inline-flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Télécharger le document actuel
                                    </a>
                                </div>
                            @endif
                            <input type="file" name="feuille_declarative_qualifications" accept=".pdf,.jpg,.jpeg,.png" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <p class="text-xs text-gray-500 mt-1">Formats acceptés : PDF, JPG, JPEG, PNG (max 100 Mo)</p>
                        </div>

                        <!-- Visite médicale classe 2 (Pilote et Instructeur) -->
                        @if(in_array($pilote->qualite, ['Pilote', 'Instructeur']))
                            <div class="bg-white p-4 rounded-lg border border-gray-200">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Visite Médicale Classe 2
                                    @if($pilote->visite_medicale_classe_2)
                                        <span class="text-green-600 text-xs ml-2">✓ Déjà fourni</span>
                                    @else
                                        <span class="text-red-600 text-xs ml-2">⚠ Manquant</span>
                                    @endif
                                </label>
                                @if($pilote->visite_medicale_classe_2)
                                    <div class="mb-2">
                                        <a href="/storage/{{ $pilote->visite_medicale_classe_2 }}" target="_blank" download class="text-blue-600 hover:text-blue-800 text-sm font-medium inline-flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            Télécharger le document actuel
                                        </a>
                                    </div>
                                @endif
                                <input type="file" name="visite_medicale_classe_2" accept=".pdf,.jpg,.jpeg,.png" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <p class="text-xs text-gray-500 mt-1">Formats acceptés : PDF, JPG, JPEG, PNG (max 100 Mo)</p>
                            </div>

                            <!-- SPL Valide -->
                            <div class="bg-white p-4 rounded-lg border border-gray-200">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    SPL Valide
                                    @if($pilote->spl_valide)
                                        <span class="text-green-600 text-xs ml-2">✓ Déjà fourni</span>
                                    @else
                                        <span class="text-red-600 text-xs ml-2">⚠ Manquant</span>
                                    @endif
                                </label>
                                @if($pilote->spl_valide)
                                    <div class="mb-2">
                                        <a href="/storage/{{ $pilote->spl_valide }}" target="_blank" download class="text-blue-600 hover:text-blue-800 text-sm font-medium inline-flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            Télécharger le document actuel
                                        </a>
                                    </div>
                                @endif
                                <input type="file" name="spl_valide" accept=".pdf,.jpg,.jpeg,.png" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <p class="text-xs text-gray-500 mt-1">Formats acceptés : PDF, JPG, JPEG, PNG (max 100 Mo)</p>
                            </div>
                        @endif

                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition duration-200">
                            Enregistrer les documents
                        </button>
                    </form>
                </div>
            </div>

            <div class="mt-6 text-center">
                <a href="{{ route('inscription.index') }}" class="text-blue-600 hover:text-blue-800">
                    Retour à l'accueil
                </a>
            </div>
        </div>
    </div>
</body>
</html>


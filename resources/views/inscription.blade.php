<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inscription - Wassmer Cup</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="icon" type="image/png" href="/img/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/img/favicon.svg" />
    <link rel="shortcut icon" href="/img/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/img/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="wassmercup" />
    <link rel="manifest" href="/img/site.webmanifest" />
    <style>
        html {
            scroll-behavior: smooth;
        }
        body {
            font-family: 'Figtree', sans-serif;
            background: linear-gradient(135deg, #f5f1e8 0%, #e8ddd4 50%, #d4c4b0 100%);
            background-attachment: fixed;
            position: relative;
        }
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('/img/wassmer cup 2026 (1).svg');
            background-size: cover;
            background-position: center top;
            background-repeat: no-repeat;
            opacity: 0.25;
            z-index: 0;
            pointer-events: none;
        }
        .container {
            position: relative;
            z-index: 1;
        }
        #adresse_suggestions {
            padding: 10px;
        }
        #adresse_suggestions .suggestion-item {
            padding: 10px 15px;
            cursor: pointer;
            border-bottom: 1px solid #e5e7eb;
        }
        #adresse_suggestions .suggestion-item:hover {
            background-color: #f3f4f6;
        }
        #adresse_suggestions .suggestion-item:last-child {
            border-bottom: none;
        }
        .etape-formulaire {
            transition: all 0.3s ease;
        }
        input[readonly], select[readonly], textarea[readonly] {
            background-color: #f3f4f6 !important;
            cursor: not-allowed !important;
        }
        input[disabled], select[disabled], textarea[disabled] {
            background-color: #f3f4f6 !important;
            cursor: not-allowed !important;
        }
        .header-title {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
            filter: drop-shadow(0 2px 4px rgba(255, 255, 255, 0.5));
        }
        .header-subtitle {
            color: #5d4e37;
            text-shadow: 1px 1px 2px rgba(255, 255, 255, 0.8);
        }
        .prix-ruban {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            font-size: 14px;
            font-weight: 600;
            line-height: 1.6;
            text-align: center;
            max-width: 200px;
            margin-top: 12px;
        }
        .prix-ruban .prix-item {
            display: block;
        }
        .prix-ruban .prix-montant {
            font-size: 16px;
            font-weight: 700;
        }
        @media (max-width: 768px) {
            .prix-ruban {
                max-width: 100%;
                margin-top: 12px;
            }
        }
    </style>
</head>
<body class="min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <!-- En-tête -->
        <header class="text-center mb-12">
            <div class="flex justify-between items-start mb-6">
                <div></div>
                <div class="flex-1">
                    <h1 class="text-6xl font-bold mb-4 header-title">Wassmer Cup</h1>
                    <p class="text-2xl font-semibold header-subtitle">Compétition de Planeur</p>
                </div>
                <div class="flex flex-col items-end">
                    <a href="{{ route('login') }}" class="text-white hover:text-gray-100 font-medium bg-gray-700 bg-opacity-90 hover:bg-opacity-100 px-4 py-2 rounded-lg transition duration-200 shadow-lg">
                        Connexion
                    </a>
                    <!-- Ruban de prix -->
                    <div class="prix-ruban">
                        <span class="prix-item">
                            <span class="prix-montant">100€</span> par planeur
                        </span>
                        <span class="prix-item">
                            Adhésion <span class="prix-montant">50€</span> par pilote
                        </span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Messages de succès/erreur -->
        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-md">
                <p class="font-bold">{{ session('success') }}</p>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-md">
                <p class="font-bold mb-2">Veuillez corriger les erreurs suivantes :</p>
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Description de l'événement -->
        <section class="bg-white rounded-lg shadow-lg mb-12 relative overflow-hidden">
            <!-- Ruban avec compteur de planeurs (affiché uniquement à partir de 5 planeurs) -->
            @if(($nombrePlaneursInscrits ?? 0) >= 5)
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white text-center py-3 px-6">
                <p class="text-lg font-bold">
                    <span class="text-2xl">{{ $nombrePlaneursInscrits ?? 0 }}</span> / {{ $limitePlaneurs ?? 15 }} planeurs inscrits
                </p>
            </div>
            @endif
            <div class="p-8">
                <div class="mb-6">
                    <h2 class="text-3xl font-bold text-gray-900 text-center">À propos de l'événement</h2>
                </div>
                
                @if($afficherRubanStats)
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg shadow-lg p-6 mb-6">
                    <div class="flex flex-col md:flex-row justify-around items-center gap-4 text-center">
                        <div class="flex-1">
                            <div class="text-sm font-medium opacity-90 mb-1">Places restantes</div>
                            <div class="text-3xl font-bold">{{ $placesRestantes }}</div>
                        </div>
                        <div class="hidden md:block w-px h-12 bg-white opacity-30"></div>
                        <div class="flex-1">
                            <div class="text-sm font-medium opacity-90 mb-1">Pilotes inscrits</div>
                            <div class="text-3xl font-bold">{{ $nombrePilotesInscrits }}</div>
                        </div>
                    </div>
                </div>
                @endif
                
            <div class="prose prose-lg max-w-none text-gray-700">
                <div class="mb-6">
                    <h3 class="text-2xl font-semibold text-gray-800 mb-4">Au programme</h3>
                    <ul class="list-disc list-inside space-y-3 text-lg">
                        <li><strong>Vols</strong> : Découvrez ou faites voler ces planeurs emblématiques.</li>
                        <li><strong>Moments conviviaux</strong> : Rencontres et discussions entre passionnés.</li>
                    </ul>
                </div>

                <div class="bg-blue-50 border-l-4 border-blue-500 p-6 my-6 rounded">
                    <h3 class="text-xl font-semibold text-blue-900 mb-4">Informations pratiques</h3>
                    <ul class="list-none space-y-3 text-blue-800">
                        <li class="flex items-start">
                            <span class="font-semibold mr-2">Dates :</span>
                            <span>27–31 juillet 2026</span>
                        </li>
                        <li class="flex items-start">
                            <span class="font-semibold mr-2">Lieu :</span>
                            <span>
                                {{ $competition->lieu ?? 'Aérodrome de thouars' }}
                                @if($competition && $competition->code_aeroport)
                                    <span class="ml-2 font-mono text-blue-600">({{ $competition->code_aeroport }})</span>
                                    <a href="#" onclick="afficherDetailsAeroport(event, '{{ $competition->code_aeroport }}')" class="ml-2 text-blue-600 hover:text-blue-800 hover:underline text-sm">
                                        Cliquez ici pour plus de détails
                                    </a>
                                @endif
                            </span>
                        </li>
                        <li class="flex items-start">
                            <span class="font-semibold mr-2">Organisé par :</span>
                            <span>le <a href="https://www.cvvt.fr" target="_blank" class="text-blue-600 hover:text-blue-800 hover:underline font-medium">Centre de vol à voile Thouarsais</a> <a href="https://www.cvvt.fr" target="_blank" class="text-blue-600 hover:text-blue-800 hover:underline">www.cvvt.fr</a></span>
                        </li>
                        <li class="flex items-start">
                            <span class="font-semibold mr-2">Places limitées :</span>
                            <span>{{ $limitePlaneurs ?? 15 }} planeurs maximum (inscription obligatoire)</span>
                        </li>
                        <li class="flex items-start">
                            <span class="font-semibold mr-2">Public :</span>
                            <span>Propriétaires et amateurs de planeurs anciens</span>
                        </li>
                        <li class="flex items-start">
                            <span class="font-semibold mr-2"></span>
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-plane text-blue-600"></i>
                                    <span class="font-semibold">Propriétaires d'avion Wassmer : vous êtes les bienvenus, sans inscription.</span>
                                </div>
                                <p class="mt-2 ml-7">
                                    Venez participer à la première rencontre dédiée au fabricant Wassmer et faites découvrir les avions Wassmer.
                                </p>
                                <p class="mt-2 ml-7">
                                    Aucune place en hangar n'est disponible : l'avion sera stationné en extérieur.
                                </p>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="bg-blue-50 border-l-4 border-blue-500 p-6 my-6 rounded">
                    <h3 class="text-xl font-semibold text-blue-900 mb-4">Points de virage</h3>
                    <p class="text-blue-800">
                        Les points de virage seront bientôt disponibles (dans quelques semaines). Une carte sera mise à disposition avec les points de virage pour la compétition.
                    </p>
                </div>
            </div>
            </div>
            <div class="text-center pb-8">
                <!-- Informations de contact -->
                <div class="mb-6 max-w-2xl mx-auto">
                    <div class="flex items-center justify-center gap-2 text-gray-700 flex-wrap">
                        <i class="fas fa-envelope text-blue-600"></i>
                        <span class="text-sm">Pour toute question :</span>
                        <a href="mailto:{{ config('mail.admin_email', 'contact@wassmercup.fr') }}" class="text-blue-600 hover:text-blue-800 hover:underline font-medium">
                            {{ config('mail.admin_email', 'contact@wassmercup.fr') }}
                        </a>
                        <span class="text-sm">ou</span>
                        <button onclick="ouvrirModalContact()" class="text-blue-600 hover:text-blue-800 hover:underline font-medium cursor-pointer">
                            formulaire de contact
                        </button>
                    </div>
                </div>
                
                <div class="flex justify-center gap-4 flex-wrap">
                    <a href="#inscription_form" onclick="scrollToInscription(event)" class="inline-block bg-gray-700 hover:bg-gray-800 text-white font-bold py-3 px-8 rounded-lg transition duration-200 shadow-lg hover:shadow-xl text-lg">
                        Inscrivez-vous
                    </a>
                    @if($competition && $competition->reglement)
                    <button onclick="ouvrirModalReglement()" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-8 rounded-lg transition duration-200 shadow-lg hover:shadow-xl text-lg flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Consulter le règlement
                    </button>
                    @endif
                </div>
            </div>
        </section>

        <!-- Formulaire d'inscription -->
        <section id="inscription_section" class="bg-white rounded-lg shadow-lg p-8">
            <span id="inscription_form" style="display: block; position: relative; top: -80px; visibility: hidden;"></span>
            <h2 class="text-3xl font-bold text-gray-900 mb-4 text-center">Formulaire d'inscription</h2>
            
            <!-- Infobox d'information -->
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm text-blue-700">
                            <strong class="font-semibold">Important :</strong> Seulement <strong>{{ $limitePlaneurs ?? 15 }} planeurs</strong> sont autorisés à l'inscription. Il n'y a <strong>pas de limite de pilotes inscrits</strong>. Cependant, les pilotes doivent s'assurer que le planeur de leur club est inscrit s'ils n'inscrivent pas de planeur.
                        </p>
                    </div>
                </div>
            </div>
            
            <form action="{{ route('inscription.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" id="inscription_form" onsubmit="reactiverChampsAvantSoumission()">
                @csrf

                <!-- Étape 1 : Informations de base du pilote -->
                <div id="etape_1" class="etape-formulaire">
                    <div class="border-b border-gray-200 pb-6">
                        <h3 class="text-2xl font-semibold text-gray-800 mb-4">Informations du pilote</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="nom" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nom <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                    id="nom" 
                                    name="nom" 
                                    value="{{ old('nom') }}"
                                    required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nom') border-red-500 @enderror">
                            </div>

                            <div>
                                <label for="prenom" class="block text-sm font-medium text-gray-700 mb-2">
                                    Prénom <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                    id="prenom" 
                                    name="prenom" 
                                    value="{{ old('prenom') }}"
                                    required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('prenom') border-red-500 @enderror">
                            </div>

                            <div>
                                <label for="qualite" class="block text-sm font-medium text-gray-700 mb-2">
                                    Qualité <span class="text-red-500">*</span>
                                </label>
                                <select id="qualite" 
                                        name="qualite" 
                                        required
                                        onchange="toggleElevePiloteWarning()"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('qualite') border-red-500 @enderror">
                                    <option value="">Sélectionnez une qualité</option>
                                    <option value="Pilote" {{ old('qualite') == 'Pilote' ? 'selected' : '' }}>Pilote</option>
                                    <option value="Élève Pilote" {{ old('qualite') == 'Élève Pilote' ? 'selected' : '' }}>Élève Pilote</option>
                                    <option value="Instructeur" {{ old('qualite') == 'Instructeur' ? 'selected' : '' }}>Instructeur</option>
                                </select>
                                
                                <!-- Avertissement pour Élève Pilote -->
                                <div id="eleve_pilote_warning" class="bg-red-50 border-l-4 border-red-500 p-3 mt-3 rounded" style="display: {{ old('qualite') == 'Élève Pilote' ? 'block' : 'none' }};">
                                    <p class="text-red-700 font-semibold text-sm">
                                        ⚠️ Attention : Vous devez prévoir un instructeur pour voler, nous ne pouvons vous garantir la disponibilité d'un instructeur. Cet instructeur doit aussi s'inscrire à l'événement.
                                    </p>
                                </div>
                            </div>

                            <div>
                                <label for="date_naissance" class="block text-sm font-medium text-gray-700 mb-2">
                                    Date de naissance <span class="text-red-500">*</span>
                                </label>
                                <input type="date" 
                                    id="date_naissance" 
                                    name="date_naissance" 
                                    value="{{ old('date_naissance') }}"
                                    required
                                    max="{{ date('Y-m-d', strtotime('-1 day')) }}"
                                    min="{{ date('Y-m-d', strtotime('-120 years')) }}"
                                    onchange="checkAgeAndToggleAutorisation()"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('date_naissance') border-red-500 @enderror">
                                <p class="mt-1 text-sm text-gray-500">
                                    Âge minimum requis : 14 ans
                                </p>
                                @error('date_naissance')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                    E-mail <span class="text-red-500">*</span>
                                </label>
                                <input type="email" 
                                    id="email" 
                                    name="email" 
                                    value="{{ old('email') }}"
                                    required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror">
                            </div>

                            <div>
                                <label for="telephone" class="block text-sm font-medium text-gray-700 mb-2">
                                    Téléphone
                                </label>
                                <input type="tel" 
                                    id="telephone" 
                                    name="telephone" 
                                    value="{{ old('telephone') }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('telephone') border-red-500 @enderror">
                            </div>

                            <div>
                                <label for="club" class="block text-sm font-medium text-gray-700 mb-2">
                                    Club
                                </label>
                                <input type="text" 
                                    id="club" 
                                    name="club" 
                                    value="{{ old('club') }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('club') border-red-500 @enderror">
                            </div>

                            <div class="md:col-span-2">
                                <label for="adresse_search" class="block text-sm font-medium text-gray-700 mb-2">
                                    Adresse
                                </label>
                                <div class="relative">
                                    <input type="text" 
                                        id="adresse_search" 
                                        name="adresse_search_field"
                                        autocomplete="nope"
                                        data-lpignore="true"
                                        data-form-type="other"
                                        placeholder="Rechercher une adresse..."
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('adresse') border-red-500 @enderror">
                                    <div id="adresse_suggestions" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg hidden max-h-60 overflow-y-auto"></div>
                                </div>
                                <!-- Champs cachés pour stocker les valeurs réelles -->
                                <input type="hidden" id="adresse" name="adresse" value="{{ old('adresse') }}">
                                <p class="mt-1 text-sm text-gray-500">
                                    Commencez à taper pour rechercher votre adresse
                                </p>
                                @error('adresse')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="code_postal" class="block text-sm font-medium text-gray-700 mb-2">
                                    Code postal
                                </label>
                                <input type="text" 
                                    id="code_postal" 
                                    name="code_postal" 
                                    value="{{ old('code_postal') }}"
                                    placeholder="Ex: 79000"
                                    maxlength="10"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('code_postal') border-red-500 @enderror">
                                @error('code_postal')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="ville" class="block text-sm font-medium text-gray-700 mb-2">
                                    Ville
                                </label>
                                <input type="text" 
                                    id="ville" 
                                    name="ville" 
                                    value="{{ old('ville') }}"
                                    placeholder="Ex: Niort"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('ville') border-red-500 @enderror">
                                @error('ville')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="numero_ffvp" class="block text-sm font-medium text-gray-700 mb-2">
                                    N° FFVP <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                    id="numero_ffvp" 
                                    name="numero_ffvp" 
                                    value="{{ old('numero_ffvp') }}"
                                    placeholder="Ex: 12345"
                                    required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('numero_ffvp') border-red-500 @enderror">
                                @error('numero_ffvp')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Bouton Suivant pour passer à l'étape 2 -->
                        <div class="mt-6 flex justify-end">
                            <button type="button" 
                                    id="btn_etape_1_suivant"
                                    onclick="validerEtape1()"
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 shadow-lg hover:shadow-xl">
                                Suivant →
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Étape 2 : Documents facultatifs -->
                <div id="etape_2" class="etape-formulaire" style="display: none;">
                    <div class="border-b border-gray-200 pb-6">
                        <!-- Résumé de l'étape 1 (en lecture seule) -->
                    <div id="resume_etape_1" class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-semibold text-gray-800">Informations du pilote</h3>
                            <button type="button" 
                                    onclick="editerEtape1()"
                                    class="text-blue-600 hover:text-blue-800 font-medium text-sm">
                                Éditer
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div><strong>Nom :</strong> <span id="resume_nom"></span></div>
                            <div><strong>Prénom :</strong> <span id="resume_prenom"></span></div>
                            <div><strong>Qualité :</strong> <span id="resume_qualite"></span></div>
                            <div><strong>Date de naissance :</strong> <span id="resume_date_naissance"></span></div>
                            <div><strong>Email :</strong> <span id="resume_email"></span></div>
                            <div><strong>Téléphone :</strong> <span id="resume_telephone"></span></div>
                            <div><strong>Club :</strong> <span id="resume_club"></span></div>
                            <div><strong>N° FFVP :</strong> <span id="resume_numero_ffvp"></span></div>
                            <div class="md:col-span-2"><strong>Adresse :</strong> <span id="resume_adresse"></span></div>
                            <div><strong>Code postal :</strong> <span id="resume_code_postal"></span></div>
                            <div><strong>Ville :</strong> <span id="resume_ville"></span></div>
                        </div>
                    </div>
                    </div>

                    <!-- Documents facultatifs -->
                    <div class="mt-6">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">Documents supplémentaires (peuvent être fournis plus tard)</h4>
                        
                        <!-- Autorisation parentale pour les mineurs -->
                        <div id="autorisation_parentale_section" class="mb-4" style="display: none;">
                            <label for="autorisation_parentale" class="block text-sm font-medium text-gray-700 mb-2">
                                Autorisation parentale signée <span id="autorisation_parentale_required" class="text-red-500">*</span>
                            </label>
                            <div class="mb-3">
                                <a href="{{ asset('models/modele-autorisation-parentale.pdf') }}" 
                                   target="_blank"
                                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Télécharger le modèle PDF
                                </a>
                            </div>
                            <input type="file" 
                                   id="autorisation_parentale" 
                                   name="autorisation_parentale" 
                                   accept=".pdf,.jpg,.jpeg,.png"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('autorisation_parentale') border-red-500 @enderror">
                            <p class="mt-2 text-sm text-gray-500">
                                Formats acceptés : PDF, JPG, JPEG, PNG (max 100 Mo)
                            </p>
                            <p id="autorisation_parentale_obligatoire" class="mt-2 text-sm text-red-600 font-semibold" style="display: none;">
                                Ce document est obligatoire pour les mineurs (moins de 18 ans).
                            </p>
                            @error('autorisation_parentale')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Feuille déclarative qualifications (pour tous) -->
                        <div class="mb-4">
                            <label for="feuille_declarative_qualifications" class="block text-sm font-medium text-gray-700 mb-2">
                                Feuille déclarative attestant que le pilote sera à jour de ses qualifications durant la rencontre
                            </label>
                            <div class="mb-3">
                                <a href="{{ asset('models/modele-feuille-declarative-qualifications.pdf') }}" 
                                   target="_blank"
                                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Télécharger le modèle PDF
                                </a>
                            </div>
                            <input type="file" 
                                   id="feuille_declarative_qualifications" 
                                   name="feuille_declarative_qualifications" 
                                   accept=".pdf,.jpg,.jpeg,.png"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('feuille_declarative_qualifications') border-red-500 @enderror">
                            <p class="mt-2 text-sm text-gray-500">
                                Formats acceptés : PDF, JPG, JPEG, PNG (max 100 Mo)
                            </p>
                            @error('feuille_declarative_qualifications')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Visite Médicale Classe 2 (pour Pilote et Instructeur uniquement) -->
                        <div id="visite_medicale_section" class="mb-4" style="display: none;">
                            <label for="visite_medicale_classe_2" class="block text-sm font-medium text-gray-700 mb-2">
                                Visite Médicale Classe 2
                            </label>
                            <input type="file" 
                                   id="visite_medicale_classe_2" 
                                   name="visite_medicale_classe_2" 
                                   accept=".pdf,.jpg,.jpeg,.png"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('visite_medicale_classe_2') border-red-500 @enderror">
                            <p class="mt-2 text-sm text-gray-500">
                                Formats acceptés : PDF, JPG, JPEG, PNG (max 100 Mo)
                            </p>
                            @error('visite_medicale_classe_2')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- SPL Valide (pour Pilote et Instructeur uniquement) -->
                        <div id="spl_valide_section" class="mb-4" style="display: none;">
                            <label for="spl_valide" class="block text-sm font-medium text-gray-700 mb-2">
                                SPL Valide
                            </label>
                            <input type="file" 
                                   id="spl_valide" 
                                   name="spl_valide" 
                                   accept=".pdf,.jpg,.jpeg,.png"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('spl_valide') border-red-500 @enderror">
                            <p class="mt-2 text-sm text-gray-500">
                                Formats acceptés : PDF, JPG, JPEG, PNG (max 100 Mo)
                            </p>
                            @error('spl_valide')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Bouton de navigation -->
                    <div class="mt-6 flex justify-end">
                        <button type="button" 
                                id="btn_etape_2_suivant"
                                onclick="validerEtape2()"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 shadow-lg hover:shadow-xl">
                            Suivant →
                        </button>
                    </div>
                </div>

                <!-- Étape 3 : Informations du planeur -->
                <div id="etape_3" class="etape-formulaire" style="display: none;">
                    <div class="border-b border-gray-200 pb-6">
                        <!-- Résumé de l'étape 1 (réutilisé depuis l'étape 2) -->
                        <div id="resume_etape_1_etape3_wrapper" style="display: none;">
                            <!-- Le résumé sera cloné ici depuis resume_etape_1 -->
                        </div>
                        <!-- Résumé de l'étape 2 (en lecture seule) -->
                        <div id="resume_etape_2" class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-xl font-semibold text-gray-800">Documents</h3>
                                <button type="button" 
                                        onclick="editerEtape2()"
                                        class="text-blue-600 hover:text-blue-800 font-medium text-sm">
                                    Éditer
                                </button>
                            </div>
                            <div class="text-sm">
                                <div id="resume_documents" class="space-y-2">
                                    <!-- Les documents seront affichés ici -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informations du planeur -->
                    <div class="pt-6">
                        <h3 class="text-2xl font-semibold text-gray-800 mb-4">Informations du planeur</h3>
                        
                        @if(isset($placesRestantes))
                        @if($complet)
                            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4 rounded">
                                <p class="text-red-700 font-semibold">⚠️ Toutes les places pour les planeurs sont complètes ({{ $limitePlaneurs ?? 15 }}/{{ $limitePlaneurs ?? 15 }}).</p>
                            </div>
                        @else
                            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-4 rounded">
                                <p class="text-blue-700">
                                    <strong>{{ $placesRestantes }}</strong> place{{ $placesRestantes > 1 ? 's' : '' }} restante{{ $placesRestantes > 1 ? 's' : '' }} pour les planeurs ({{ ($limitePlaneurs ?? 15) - $placesRestantes }}/{{ $limitePlaneurs ?? 15 }})
                                </p>
                            </div>
                        @endif
                        @endif

                        <!-- Choix du type d'inscription -->
                        <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-3">Votre planeur est-il déjà inscrit ?</label>
                        <div class="space-y-3">
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" 
                                       name="type_inscription_planeur" 
                                       value="existant"
                                       {{ old('type_inscription_planeur') == 'existant' ? 'checked' : '' }}
                                       class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                       onchange="toggleTypeInscription()">
                                <span class="ml-2 text-gray-700">Oui, mon planeur est déjà inscrit</span>
                            </label>
                            <label class="flex items-center cursor-pointer {{ ($complet ?? false) ? 'opacity-50 cursor-not-allowed' : '' }}">
                                <input type="radio" 
                                       name="type_inscription_planeur" 
                                       value="nouveau"
                                       {{ ($complet ?? false) ? 'disabled' : '' }}
                                       {{ old('type_inscription_planeur') == 'nouveau' ? 'checked' : '' }}
                                       class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                       onchange="toggleTypeInscription()">
                                <span class="ml-2 text-gray-700">Non, je souhaite inscrire un nouveau planeur</span>
                            </label>
                        </div>
                        </div>

                        <!-- Sélection d'un planeur existant -->
                        <div id="planeur_existant_section" style="display: {{ old('type_inscription_planeur') == 'existant' ? 'block' : 'none' }};" class="mb-6">
                        <label for="planeur_existant_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Sélectionnez votre planeur <span class="text-red-500">*</span>
                        </label>
                        <select id="planeur_existant_id" 
                                name="planeur_existant_id" 
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('planeur_existant_id') border-red-500 @enderror">
                            <option value="">Sélectionnez un planeur</option>
                            @foreach($planeursExistants ?? [] as $planeur)
                                <option value="{{ $planeur['id'] }}" {{ old('planeur_existant_id') == $planeur['id'] ? 'selected' : '' }}>
                                    {{ $planeur['label'] }}
                                </option>
                            @endforeach
                        </select>
                        </div>
                        
                        <!-- Formulaire d'inscription d'un nouveau planeur -->
                        <div id="planeur_nouveau_section" style="display: {{ old('type_inscription_planeur') == 'nouveau' ? 'block' : 'none' }};" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="marque" class="block text-sm font-medium text-gray-700 mb-2">
                                Marque
                            </label>
                            <input type="text" 
                                   id="marque" 
                                   name="marque" 
                                   value="{{ old('marque') }}"
                                   placeholder="Ex : Wassmer"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('marque') border-red-500 @enderror">
                        </div>

                        <div>
                            <label for="modele" class="block text-sm font-medium text-gray-700 mb-2">
                                Modèle <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="modele" 
                                   name="modele" 
                                   value="{{ old('modele') }}"
                                   placeholder="Ex : WA22"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('modele') border-red-500 @enderror">
                        </div>

                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                                Type
                            </label>
                            <select id="type" 
                                    name="type" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('type') border-red-500 @enderror">
                                <option value="">Sélectionnez un type</option>
                                <option value="plastique" {{ old('type') == 'plastique' ? 'selected' : '' }}>Plastique</option>
                                <option value="bois & toiles" {{ old('type') == 'bois & toiles' ? 'selected' : '' }}>Bois & toiles</option>
                            </select>
                        </div>

                        <div>
                            <label for="immatriculation" class="block text-sm font-medium text-gray-700 mb-2">
                                Immatriculation <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="immatriculation" 
                                   name="immatriculation" 
                                   value="{{ old('immatriculation') }}"
                                   placeholder="Ex: F-CXXX"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('immatriculation') border-red-500 @enderror">
                        </div>
                        </div>

                        <!-- Documents facultatifs pour le planeur (si un planeur est inscrit) -->
                        <div id="documents_planeur_section" class="mt-6" style="display: none;">
                            <h4 class="text-lg font-semibold text-gray-800 mb-4">Documents du planeur (peuvent être fournis plus tard)</h4>
                            
                            <!-- CDN / CEN -->
                            <div class="mb-4">
                                <label for="cdn_cen" class="block text-sm font-medium text-gray-700 mb-2">
                                    CDN / CEN
                                </label>
                                <input type="file" 
                                       id="cdn_cen" 
                                       name="cdn_cen" 
                                       accept=".pdf,.jpg,.jpeg,.png"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('cdn_cen') border-red-500 @enderror">
                                <p class="mt-2 text-sm text-gray-500">
                                    Formats acceptés : PDF, JPG, JPEG, PNG (max 100 Mo)
                                </p>
                                @error('cdn_cen')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Responsabilité civile -->
                            <div class="mb-4">
                                <label for="responsabilite_civile" class="block text-sm font-medium text-gray-700 mb-2">
                                    Responsabilité civile
                                </label>
                                <input type="file" 
                                       id="responsabilite_civile" 
                                       name="responsabilite_civile" 
                                       accept=".pdf,.jpg,.jpeg,.png"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('responsabilite_civile') border-red-500 @enderror">
                                <p class="mt-2 text-sm text-gray-500">
                                    Formats acceptés : PDF, JPG, JPEG, PNG (max 100 Mo)
                                </p>
                                @error('responsabilite_civile')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Bouton de soumission -->
                    <div class="pt-6 flex justify-end">
                        <button type="submit" 
                                class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 shadow-lg hover:shadow-xl">
                            S'inscrire à la compétition
                        </button>
                    </div>
                </div>

                <p class="text-sm text-gray-500 text-center mt-4">
                    <span class="text-red-500">*</span> Champs obligatoires
                </p>
            </form>
        </section>

        <!-- Pied de page -->
        <footer class="text-center mt-12 text-gray-600">
            <p>&copy; {{ date('Y') }} Wassmer Cup - Tous droits réservés</p>
        </footer>
    </div>

    <script>
        function checkAgeAndToggleAutorisation() {
            const dateNaissance = document.getElementById('date_naissance').value;
            const autorisationSection = document.getElementById('autorisation_parentale_section');
            const autorisationInput = document.getElementById('autorisation_parentale');
            const autorisationRequired = document.getElementById('autorisation_parentale_required');
            const autorisationObligatoire = document.getElementById('autorisation_parentale_obligatoire');
            
            if (dateNaissance && autorisationSection && autorisationInput) {
                const birthDate = new Date(dateNaissance);
                const today = new Date();
                let age = today.getFullYear() - birthDate.getFullYear();
                const monthDiff = today.getMonth() - birthDate.getMonth();
                
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                
                if (age < 18) {
                    // Mineur : afficher le champ et le rendre obligatoire
                    autorisationSection.style.display = 'block';
                    autorisationInput.setAttribute('required', 'required');
                    if (autorisationRequired) {
                        autorisationRequired.style.display = 'inline';
                    }
                    if (autorisationObligatoire) {
                        autorisationObligatoire.style.display = 'block';
                    }
                } else {
                    // Majeur : masquer le champ
                    autorisationSection.style.display = 'none';
                    autorisationInput.removeAttribute('required');
                    autorisationInput.value = ''; // Vider le champ si rempli
                    if (autorisationRequired) {
                        autorisationRequired.style.display = 'none';
                    }
                    if (autorisationObligatoire) {
                        autorisationObligatoire.style.display = 'none';
                    }
                }
            } else if (autorisationSection) {
                // Pas de date de naissance : masquer le champ
                autorisationSection.style.display = 'none';
                if (autorisationInput) {
                    autorisationInput.removeAttribute('required');
                }
                if (autorisationRequired) {
                    autorisationRequired.style.display = 'none';
                }
                if (autorisationObligatoire) {
                    autorisationObligatoire.style.display = 'none';
                }
            }
        }

        function toggleElevePiloteWarning() {
            const qualite = document.getElementById('qualite');
            const warning = document.getElementById('eleve_pilote_warning');
            
            if (qualite.value === 'Élève Pilote') {
                warning.style.display = 'block';
            } else {
                warning.style.display = 'none';
            }
        }

        function toggleDocumentsFacultatifs() {
            const qualite = document.getElementById('qualite');
            const visiteMedicaleSection = document.getElementById('visite_medicale_section');
            const splValideSection = document.getElementById('spl_valide_section');
            
            // Afficher les champs pour Pilote et Instructeur uniquement
            if (qualite.value === 'Pilote' || qualite.value === 'Instructeur') {
                visiteMedicaleSection.style.display = 'block';
                splValideSection.style.display = 'block';
            } else {
                visiteMedicaleSection.style.display = 'none';
                splValideSection.style.display = 'none';
                // Vider les champs si masqués
                document.getElementById('visite_medicale_classe_2').value = '';
                document.getElementById('spl_valide').value = '';
            }
        }

        function toggleTypeInscription() {
            const typeExistant = document.querySelector('input[name="type_inscription_planeur"][value="existant"]');
            const typeNouveau = document.querySelector('input[name="type_inscription_planeur"][value="nouveau"]');
            const sectionExistant = document.getElementById('planeur_existant_section');
            const sectionNouveau = document.getElementById('planeur_nouveau_section');
            const documentsPlaneurSection = document.getElementById('documents_planeur_section');
            const selectExistant = document.getElementById('planeur_existant_id');
            const modele = document.getElementById('modele');
            const immatriculation = document.getElementById('immatriculation');

            if (typeExistant && typeExistant.checked) {
                sectionExistant.style.display = 'block';
                sectionNouveau.style.display = 'none';
                // Masquer les documents du planeur si c'est un planeur existant
                if (documentsPlaneurSection) {
                    documentsPlaneurSection.style.display = 'none';
                }
                selectExistant.setAttribute('required', 'required');
                modele.removeAttribute('required');
                immatriculation.removeAttribute('required');
                // Vider les champs du nouveau planeur
                modele.value = '';
                immatriculation.value = '';
                document.getElementById('marque').value = '';
                document.getElementById('type').value = '';
                // Vider les champs des documents du planeur
                const cdnCen = document.getElementById('cdn_cen');
                const responsabiliteCivile = document.getElementById('responsabilite_civile');
                if (cdnCen) cdnCen.value = '';
                if (responsabiliteCivile) responsabiliteCivile.value = '';
            } else if (typeNouveau && typeNouveau.checked) {
                sectionExistant.style.display = 'none';
                sectionNouveau.style.display = 'grid';
                // Afficher les documents du planeur si c'est un nouveau planeur
                if (documentsPlaneurSection) {
                    documentsPlaneurSection.style.display = 'block';
                }
                selectExistant.removeAttribute('required');
                modele.setAttribute('required', 'required');
                immatriculation.setAttribute('required', 'required');
                // Vider la sélection du planeur existant
                selectExistant.value = '';
            } else {
                sectionExistant.style.display = 'none';
                sectionNouveau.style.display = 'none';
                if (documentsPlaneurSection) {
                    documentsPlaneurSection.style.display = 'none';
                }
                selectExistant.removeAttribute('required');
                modele.removeAttribute('required');
                immatriculation.removeAttribute('required');
            }
        }

        // Fonction pour l'autocomplétion d'adresse avec l'API IGN
        let adresseSearchTimeout;
        const adresseSearchInput = document.getElementById('adresse_search');
        const adresseSuggestions = document.getElementById('adresse_suggestions');
        const adresseHiddenInput = document.getElementById('adresse');
        const codePostalInput = document.getElementById('code_postal');
        const villeInput = document.getElementById('ville');

        function initAdresseAutocomplete() {
            if (!adresseSearchInput) return;

            // Remplir le champ de recherche si une valeur existe déjà
            if (adresseHiddenInput.value) {
                adresseSearchInput.value = adresseHiddenInput.value;
            }

            adresseSearchInput.addEventListener('input', function() {
                const query = this.value.trim();
                
                // Masquer les suggestions si le champ est vide
                if (query.length < 3) {
                    adresseSuggestions.classList.add('hidden');
                    return;
                }

                // Délai pour éviter trop de requêtes
                clearTimeout(adresseSearchTimeout);
                adresseSearchTimeout = setTimeout(() => {
                    searchAdresse(query);
                }, 300);
            });

            // Ne pas masquer les suggestions automatiquement - elles resteront affichées jusqu'à la sélection
        }

        function searchAdresse(query) {
            // Utilisation de l'API IGN Géoplateforme pour l'autocomplétion (sans clé API nécessaire)
            // Endpoint d'autocomplétion IGN - API publique gratuite
            const apiUrl = `https://data.geopf.fr/geocodage/search?q=${encodeURIComponent(query)}&autocomplete=1&index=address&limit=10&returntruegeometry=false`;
            
            fetch(apiUrl, {
                method: 'GET',
                headers: {
                    'accept': 'application/json'
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur HTTP');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.features && data.features.length > 0) {
                        displaySuggestionsIGN(data.features);
                    } else {
                        // Fallback sur l'API BAN si l'API IGN ne retourne rien
                        fallbackSearchAdresse(query);
                    }
                })
                .catch(error => {
                    console.error('Erreur lors de la recherche d\'adresse IGN:', error);
                    // Fallback sur l'API BAN en cas d'erreur
                    fallbackSearchAdresse(query);
                });
        }

        function fallbackSearchAdresse(query) {
            // Fallback sur l'API Adresse de la BAN (gratuite, française)
            const apiUrl = `https://api-adresse.data.gouv.fr/search/?q=${encodeURIComponent(query)}&limit=5`;
            
            fetch(apiUrl)
                .then(response => response.json())
                .then(data => {
                    displaySuggestions(data.features || []);
                })
                .catch(error => {
                    console.error('Erreur lors de la recherche d\'adresse:', error);
                    adresseSuggestions.classList.add('hidden');
                });
        }

        function displaySuggestionsIGN(features) {
            if (features.length === 0) {
                adresseSuggestions.classList.add('hidden');
                return;
            }

            adresseSuggestions.innerHTML = '';
            features.forEach(feature => {
                // Format de réponse API IGN Géoplateforme (FeatureCollection)
                const properties = feature.properties || {};
                const label = properties.label || ''; // Adresse complète avec code postal et ville (pour affichage)
                const name = properties.name || ''; // Adresse seule sans code postal ni ville
                const codePostal = properties.postcode || '';
                const ville = properties.city || '';
                
                if (!label) return; // Ignorer les résultats sans label
                
                const item = document.createElement('div');
                item.className = 'suggestion-item';
                item.textContent = label; // Afficher l'adresse complète dans les suggestions
                item.addEventListener('click', function() {
                    // Utiliser 'name' pour l'adresse seule (sans code postal ni ville)
                    // ou 'label' si 'name' n'est pas disponible
                    const adresseValue = name || label;
                    selectAdresse(adresseValue, codePostal, ville);
                });
                adresseSuggestions.appendChild(item);
            });

            adresseSuggestions.classList.remove('hidden');
        }

        function displaySuggestions(features) {
            if (features.length === 0) {
                adresseSuggestions.classList.add('hidden');
                return;
            }

            adresseSuggestions.innerHTML = '';
            features.forEach(feature => {
                const properties = feature.properties;
                const adresseComplete = properties.label || '';
                const codePostal = properties.postcode || '';
                const ville = properties.city || properties.name || '';
                
                const item = document.createElement('div');
                item.className = 'suggestion-item';
                item.textContent = adresseComplete;
                item.addEventListener('click', function() {
                    selectAdresse(adresseComplete, codePostal, ville);
                });
                adresseSuggestions.appendChild(item);
            });

            adresseSuggestions.classList.remove('hidden');
        }

        function selectAdresse(adresse, codePostal, ville) {
            adresseSearchInput.value = adresse;
            adresseHiddenInput.value = adresse;
            codePostalInput.value = codePostal;
            villeInput.value = ville;
            adresseSuggestions.classList.add('hidden');
        }

        // Fonctions de navigation entre les étapes
        function validerEtape1() {
            // Valider les champs obligatoires de l'étape 1
            const nom = document.getElementById('nom').value.trim();
            const prenom = document.getElementById('prenom').value.trim();
            const qualite = document.getElementById('qualite').value;
            const dateNaissance = document.getElementById('date_naissance').value;
            const email = document.getElementById('email').value.trim();
            const numeroFfvp = document.getElementById('numero_ffvp').value.trim();

            if (!nom || !prenom || !qualite || !dateNaissance || !email || !numeroFfvp) {
                alert('Veuillez remplir tous les champs obligatoires de l\'étape 1.');
                return;
            }

            // Créer le résumé de l'étape 1
            document.getElementById('resume_nom').textContent = nom;
            document.getElementById('resume_prenom').textContent = prenom;
            document.getElementById('resume_qualite').textContent = qualite;
            document.getElementById('resume_date_naissance').textContent = dateNaissance ? new Date(dateNaissance).toLocaleDateString('fr-FR') : '';
            document.getElementById('resume_email').textContent = email;
            document.getElementById('resume_telephone').textContent = document.getElementById('telephone').value || 'Non renseigné';
            document.getElementById('resume_club').textContent = document.getElementById('club').value || 'Non renseigné';
            document.getElementById('resume_numero_ffvp').textContent = numeroFfvp;
            
            const adresse = document.getElementById('adresse').value || document.getElementById('adresse_search').value || '';
            const codePostal = document.getElementById('code_postal').value || '';
            const ville = document.getElementById('ville').value || '';
            const adresseComplete = adresse + (codePostal ? ' ' + codePostal : '') + (ville ? ' ' + ville : '');
            document.getElementById('resume_adresse').textContent = adresseComplete || 'Non renseignée';
            document.getElementById('resume_code_postal').textContent = codePostal || 'Non renseigné';
            document.getElementById('resume_ville').textContent = ville || 'Non renseignée';

            // Figer les champs de l'étape 1
            const champsEtape1Text = ['nom', 'prenom', 'date_naissance', 'email', 'telephone', 'club', 'adresse_search', 'adresse', 'code_postal', 'ville', 'numero_ffvp'];
            champsEtape1Text.forEach(id => {
                const champ = document.getElementById(id);
                if (champ) {
                    champ.setAttribute('readonly', 'readonly');
                    champ.classList.add('bg-gray-100', 'cursor-not-allowed');
                }
            });
            
            // Pour les select, utiliser disabled
            const qualiteSelect = document.getElementById('qualite');
            if (qualiteSelect) {
                qualiteSelect.setAttribute('disabled', 'disabled');
                qualiteSelect.classList.add('bg-gray-100', 'cursor-not-allowed');
            }

            // Masquer l'étape 1 et afficher l'étape 2
            document.getElementById('etape_1').style.display = 'none';
            document.getElementById('etape_2').style.display = 'block';
            
            // Vérifier l'âge pour l'autorisation parentale maintenant qu'on est à l'étape 2
            checkAgeAndToggleAutorisation();
        }

        function editerEtape1() {
            // Défiger les champs de l'étape 1
            const champsEtape1Text = ['nom', 'prenom', 'date_naissance', 'email', 'telephone', 'club', 'adresse_search', 'adresse', 'code_postal', 'ville', 'numero_ffvp'];
            champsEtape1Text.forEach(id => {
                const champ = document.getElementById(id);
                if (champ) {
                    champ.removeAttribute('readonly');
                    champ.classList.remove('bg-gray-100', 'cursor-not-allowed');
                }
            });
            
            const qualiteSelect = document.getElementById('qualite');
            if (qualiteSelect) {
                qualiteSelect.removeAttribute('disabled');
                qualiteSelect.classList.remove('bg-gray-100', 'cursor-not-allowed');
            }

            // Masquer toutes les autres étapes et afficher uniquement l'étape 1
            document.getElementById('etape_2').style.display = 'none';
            document.getElementById('etape_3').style.display = 'none';
            document.getElementById('etape_1').style.display = 'block';
        }

        function validerEtape2() {
            // Créer le résumé des documents
            const resumeDocuments = document.getElementById('resume_documents');
            resumeDocuments.innerHTML = '';

            // Calculer l'âge pour déterminer si on doit afficher l'autorisation parentale
            const dateNaissance = document.getElementById('date_naissance').value;
            let estMineur = false;
            if (dateNaissance) {
                const birthDate = new Date(dateNaissance);
                const today = new Date();
                let age = today.getFullYear() - birthDate.getFullYear();
                const monthDiff = today.getMonth() - birthDate.getMonth();
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                estMineur = age < 18;
            }

            const documents = [
                { id: 'feuille_declarative_qualifications', label: 'Feuille déclarative qualifications' },
                { id: 'visite_medicale_classe_2', label: 'Visite Médicale Classe 2' },
                { id: 'spl_valide', label: 'SPL Valide' }
            ];

            // Ajouter l'autorisation parentale uniquement si mineur
            if (estMineur) {
                documents.push({ id: 'autorisation_parentale', label: 'Autorisation parentale' });
            }

            documents.forEach(doc => {
                const input = document.getElementById(doc.id);
                if (input && input.files && input.files.length > 0) {
                    const div = document.createElement('div');
                    div.className = 'flex items-center';
                    div.innerHTML = `<span class="text-green-600 mr-2">✓</span> <span>${doc.label}: ${input.files[0].name}</span>`;
                    resumeDocuments.appendChild(div);
                } else {
                    const div = document.createElement('div');
                    div.className = 'flex items-center text-gray-500';
                    div.innerHTML = `<span class="mr-2">-</span> <span>${doc.label}: Non fourni</span>`;
                    resumeDocuments.appendChild(div);
                }
            });

            // Figer les champs de l'étape 2 (les rendre disabled)
            const champsEtape2 = ['feuille_declarative_qualifications', 'visite_medicale_classe_2', 'spl_valide', 'autorisation_parentale'];
            champsEtape2.forEach(id => {
                const champ = document.getElementById(id);
                if (champ) {
                    // Vérifier si le champ est visible avant de le désactiver
                    const section = champ.closest('[style*="display"]') || champ.parentElement;
                    const isVisible = champ.offsetParent !== null && 
                                     champ.style.display !== 'none' && 
                                     (!section || section.style.display !== 'none');
                    
                    if (isVisible) {
                        champ.setAttribute('disabled', 'disabled');
                        champ.classList.add('bg-gray-100', 'cursor-not-allowed');
                    }
                }
            });

            // Cloner le résumé de l'étape 1 dans l'étape 3
            const resumeEtape1 = document.getElementById('resume_etape_1');
            const resumeEtape1Wrapper = document.getElementById('resume_etape_1_etape3_wrapper');
            if (resumeEtape1 && resumeEtape1Wrapper) {
                // Vider le wrapper précédent
                resumeEtape1Wrapper.innerHTML = '';
                // Cloner le résumé
                const clone = resumeEtape1.cloneNode(true);
                clone.id = 'resume_etape_1_clone';
                resumeEtape1Wrapper.appendChild(clone);
                resumeEtape1Wrapper.style.display = 'block';
            }

            // Masquer l'étape 2 et afficher l'étape 3
            document.getElementById('etape_2').style.display = 'none';
            document.getElementById('etape_3').style.display = 'block';
        }

        function editerEtape2() {
            // Défiger les champs de l'étape 2
            const champsEtape2 = ['feuille_declarative_qualifications', 'visite_medicale_classe_2', 'spl_valide', 'autorisation_parentale'];
            champsEtape2.forEach(id => {
                const champ = document.getElementById(id);
                if (champ) {
                    champ.removeAttribute('disabled');
                    champ.classList.remove('bg-gray-100', 'cursor-not-allowed');
                }
            });

            // Masquer l'étape 3 et afficher l'étape 2
            document.getElementById('etape_3').style.display = 'none';
            document.getElementById('etape_2').style.display = 'block';
        }

        // Réactiver tous les champs avant la soumission du formulaire
        function reactiverChampsAvantSoumission() {
            // Réactiver tous les champs disabled pour qu'ils soient envoyés avec le formulaire
            const tousLesChamps = document.querySelectorAll('#inscription_form input[disabled], #inscription_form select[disabled], #inscription_form textarea[disabled]');
            tousLesChamps.forEach(champ => {
                champ.removeAttribute('disabled');
            });
        }

        // Fonction pour scroller vers le formulaire avec un offset
        function scrollToInscription(event) {
            event.preventDefault();
            const section = document.getElementById('inscription_section');
            if (section) {
                const offset = 100; // Offset en pixels pour laisser voir le titre
                const elementPosition = section.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - offset;
                
                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        }

        // Initialiser l'état au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            toggleTypeInscription();
            toggleElevePiloteWarning();
            toggleDocumentsFacultatifs();
            checkAgeAndToggleAutorisation();
            initAdresseAutocomplete();
            
            // Écouter les changements de qualité
            document.getElementById('qualite').addEventListener('change', function() {
                toggleElevePiloteWarning();
                toggleDocumentsFacultatifs();
            });
        });
    </script>

    <!-- Modal Règlement -->
    <div id="modalReglement" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-4/5 lg:w-3/4 shadow-lg rounded-md bg-white" style="max-height: 90vh;">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-900">Règlement de la compétition</h3>
                <button onclick="fermerModalReglement()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="border rounded-lg overflow-hidden" style="height: calc(90vh - 120px);">
                @if($competition && $competition->reglement)
                    <iframe src="{{ route('reglement.public') }}" class="w-full h-full" frameborder="0">
                        <p>Votre navigateur ne supporte pas l'affichage des PDF. <a href="{{ route('reglement.public') }}" target="_blank">Télécharger le PDF</a></p>
                    </iframe>
                @else
                    <div class="p-8 text-center text-gray-500">
                        <p>Le règlement n'est pas encore disponible.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function ouvrirModalReglement() {
            document.getElementById('modalReglement').classList.remove('hidden');
        }

        function fermerModalReglement() {
            document.getElementById('modalReglement').classList.add('hidden');
        }

        // Fermer la modal en cliquant à l'extérieur
        document.getElementById('modalReglement').addEventListener('click', function(e) {
            if (e.target === this) {
                fermerModalReglement();
            }
        });

        // Fermer la modal avec Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !document.getElementById('modalReglement').classList.contains('hidden')) {
                fermerModalReglement();
            }
            if (e.key === 'Escape' && !document.getElementById('modalContact').classList.contains('hidden')) {
                fermerModalContact();
            }
        });
    </script>

    <!-- Modal Contact -->
    <div id="modalContact" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-900">Formulaire de contact</h3>
                <button onclick="fermerModalContact()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="contactForm" onsubmit="envoyerContact(event)">
                <div class="mb-4">
                    <label for="contact_nom" class="block text-sm font-medium text-gray-700 mb-2">
                        Nom <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="contact_nom" name="nom" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Votre nom">
                    <div id="contact_nom_error" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>

                <div class="mb-4">
                    <label for="contact_email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email" id="contact_email" name="email" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="votre@email.com">
                    <div id="contact_email_error" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>

                <div class="mb-4">
                    <label for="contact_message" class="block text-sm font-medium text-gray-700 mb-2">
                        Message <span class="text-red-500">*</span>
                    </label>
                    <textarea id="contact_message" name="message" required rows="6"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Votre message..."></textarea>
                    <div id="contact_message_error" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>

                <div id="contact_success" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 hidden"></div>
                <div id="contact_error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 hidden"></div>

                <div class="flex justify-end gap-4">
                    <button type="button" onclick="fermerModalContact()"
                        class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
                        Annuler
                    </button>
                    <button type="submit" id="contact_submit_btn"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
                        Envoyer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function ouvrirModalContact() {
            document.getElementById('modalContact').classList.remove('hidden');
            // Réinitialiser le formulaire
            document.getElementById('contactForm').reset();
            document.getElementById('contact_success').classList.add('hidden');
            document.getElementById('contact_error').classList.add('hidden');
            // Masquer les erreurs de champs
            ['contact_nom', 'contact_email', 'contact_message'].forEach(id => {
                const errorDiv = document.getElementById(id + '_error');
                if (errorDiv) {
                    errorDiv.classList.add('hidden');
                    errorDiv.textContent = '';
                }
            });
        }

        function fermerModalContact() {
            document.getElementById('modalContact').classList.add('hidden');
        }

        // Fermer la modal en cliquant à l'extérieur
        document.getElementById('modalContact').addEventListener('click', function(e) {
            if (e.target === this) {
                fermerModalContact();
            }
        });

        function envoyerContact(event) {
            event.preventDefault();
            
            // Masquer les messages précédents
            document.getElementById('contact_success').classList.add('hidden');
            document.getElementById('contact_error').classList.add('hidden');
            
            // Masquer les erreurs de champs
            ['contact_nom', 'contact_email', 'contact_message'].forEach(id => {
                const errorDiv = document.getElementById(id + '_error');
                if (errorDiv) {
                    errorDiv.classList.add('hidden');
                    errorDiv.textContent = '';
                }
            });

            // Désactiver le bouton d'envoi
            const submitBtn = document.getElementById('contact_submit_btn');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Envoi en cours...';

            // Récupérer les données du formulaire
            const formData = {
                nom: document.getElementById('contact_nom').value,
                email: document.getElementById('contact_email').value,
                message: document.getElementById('contact_message').value,
            };

            // Envoyer la requête
            fetch('{{ route("contact.send") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Afficher le message de succès
                    document.getElementById('contact_success').textContent = data.message;
                    document.getElementById('contact_success').classList.remove('hidden');
                    // Réinitialiser le formulaire
                    document.getElementById('contactForm').reset();
                    // Fermer la modal après 2 secondes
                    setTimeout(() => {
                        fermerModalContact();
                    }, 2000);
                } else {
                    // Afficher les erreurs
                    if (data.errors) {
                        // Erreurs de validation
                        Object.keys(data.errors).forEach(field => {
                            const errorDiv = document.getElementById('contact_' + field + '_error');
                            if (errorDiv) {
                                errorDiv.textContent = data.errors[field][0];
                                errorDiv.classList.remove('hidden');
                            }
                        });
                    } else {
                        // Erreur générale
                        document.getElementById('contact_error').textContent = data.message || 'Une erreur est survenue lors de l\'envoi du message.';
                        document.getElementById('contact_error').classList.remove('hidden');
                    }
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                document.getElementById('contact_error').textContent = 'Une erreur est survenue lors de l\'envoi du message. Veuillez réessayer.';
                document.getElementById('contact_error').classList.remove('hidden');
            })
            .finally(() => {
                // Réactiver le bouton
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            });
        }

        // Fonction pour afficher les détails de l'aérodrome
        function afficherDetailsAeroport(event, icaoCode) {
            event.preventDefault();
            const modal = document.getElementById('modalAeroport');
            const content = document.getElementById('modalAeroportContent');
            
            // Afficher la modal
            modal.classList.remove('hidden');
            content.innerHTML = '<p class="text-center text-gray-500">Chargement des données...</p>';
            
            // Récupérer les données de l'aérodrome
            const url = `{{ route('airport.data', ['icao' => 'PLACEHOLDER']) }}`.replace('PLACEHOLDER', icaoCode);
            
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.airport) {
                        afficherInfosAeroportPublic(data.airport);
                    } else {
                        let errorMessage = data.error || 'Données de l\'aérodrome non disponibles';
                        if (data.code_configure) {
                            errorMessage += '<br><br><small class="text-gray-600">Le code aérodrome est configuré mais les données n\'ont pas encore été récupérées depuis OpenAIP. Veuillez contacter l\'administrateur.</small>';
                        }
                        content.innerHTML = `<div class="text-center"><p class="text-red-500 mb-2">${errorMessage}</p></div>`;
                    }
                })
                .catch(error => {
                    console.error('Erreur lors du chargement des données:', error);
                    console.error('URL appelée:', url);
                    content.innerHTML = '<p class="text-center text-red-500">Erreur lors du chargement des données de l\'aérodrome. Vérifiez que le code aérodrome a été configuré dans le dashboard admin.</p>';
                });
        }

        // Fonction pour obtenir le type de surface d'une piste
        function getSurfaceType(surface) {
            if (!surface) return 'Non spécifié';
            
            const surfaceTypes = {
                0: 'Non spécifié',
                1: 'Béton/Asphalte',
                2: 'Herbe',
                3: 'Gravier',
                4: 'Terre',
                5: 'Eau',
                6: 'Neige',
                7: 'Glace',
                8: 'Béton',
                9: 'Asphalte/Goudron',
            };
            
            // Utiliser mainComposite si disponible, sinon le premier élément de composition
            let surfaceCode = null;
            if (surface.mainComposite !== undefined) {
                surfaceCode = surface.mainComposite;
            } else if (surface.composition && Array.isArray(surface.composition) && surface.composition.length > 0) {
                surfaceCode = surface.composition[0];
            }
            
            if (surfaceCode !== null && surfaceTypes[surfaceCode]) {
                return surfaceTypes[surfaceCode];
            }
            
            return 'Non spécifié';
        }

        function afficherInfosAeroportPublic(airport) {
            const content = document.getElementById('modalAeroportContent');
            
            let html = '<div class="space-y-4">';
            
            // Nom et code
            html += '<div class="bg-blue-50 p-4 rounded-lg">';
            if (airport.name) {
                html += `<h3 class="text-xl font-bold text-blue-900 mb-2">${airport.name}</h3>`;
            }
            if (airport.icaoCode) {
                html += `<p class="text-sm text-blue-700"><strong>Code ICAO:</strong> <span class="font-mono font-bold">${airport.icaoCode}</span></p>`;
            }
            if (airport.iataCode) {
                html += `<p class="text-sm text-blue-700"><strong>Code IATA:</strong> <span class="font-mono">${airport.iataCode}</span></p>`;
            }
            html += '</div>';
            
            // Carte centrée sur l'aérodrome
            if (airport.geometry && airport.geometry.coordinates) {
                const coords = airport.geometry.coordinates;
                const lat = coords[1];
                const lon = coords[0];
                
                html += '<div class="bg-gray-50 p-4 rounded-lg">';
                html += '<div class="flex justify-between items-center mb-2">';
                html += '<h4 class="font-semibold text-gray-800">Localisation</h4>';
                html += '<button onclick="ouvrirCartePleinEcran(' + lat + ', ' + lon + ', \'' + (airport.name || airport.icaoCode || 'Aérodrome') + '\', \'' + (airport.icaoCode || '') + '\')" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center gap-1">';
                html += '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>';
                html += 'Voir en grand';
                html += '</button>';
                html += '</div>';
                html += `<p class="text-sm text-gray-700 mb-3"><strong>Coordonnées GPS:</strong> ${lat}, ${lon} (latitude, longitude)</p>`;
                html += '<div id="aeroport-map" style="width: 100%; height: 300px; border-radius: 8px; overflow: hidden;"></div>';
                html += '</div>';
            } else {
                // Coordonnées sans carte si pas de géométrie
                html += '<div class="bg-gray-50 p-4 rounded-lg">';
                html += '<p class="text-sm text-gray-700">Coordonnées GPS non disponibles</p>';
                html += '</div>';
            }
            
            // Pays
            if (airport.country) {
                html += '<div class="bg-gray-50 p-4 rounded-lg">';
                html += `<p class="text-sm text-gray-700"><strong>Pays:</strong> ${airport.country}</p>`;
                html += '</div>';
            }
            
            // Altitude
            if (airport.elevation) {
                const unit = airport.elevation.unit === 0 ? 'm' : (airport.elevation.unit === 1 ? 'ft' : '');
                html += '<div class="bg-gray-50 p-4 rounded-lg">';
                html += `<p class="text-sm text-gray-700"><strong>Altitude:</strong> ${airport.elevation.value} ${unit}</p>`;
                html += '</div>';
            }
            
            // Pistes
            if (airport.runways && Array.isArray(airport.runways) && airport.runways.length > 0) {
                html += '<div class="bg-gray-50 p-4 rounded-lg">';
                html += `<h4 class="font-semibold text-gray-800 mb-2">Pistes (${airport.runways.length})</h4>`;
                airport.runways.forEach((runway) => {
                    if (runway.designator) {
                        html += '<div class="ml-4 mb-2">';
                        html += `<p class="text-sm text-gray-700"><strong>Piste ${runway.designator}</strong>`;
                        if (runway.dimension && runway.dimension.length) {
                            const unit = runway.dimension.length.unit === 0 ? 'm' : 'ft';
                            html += ` - Longueur: ${runway.dimension.length.value} ${unit}`;
                        }
                        if (runway.dimension && runway.dimension.width) {
                            const unit = runway.dimension.width.unit === 0 ? 'm' : 'ft';
                            html += ` - Largeur: ${runway.dimension.width.value} ${unit}`;
                        }
                        if (runway.surface) {
                            const surfaceType = getSurfaceType(runway.surface);
                            html += ` - Surface: ${surfaceType}`;
                        }
                        html += '</p>';
                        html += '</div>';
                    }
                });
                html += '</div>';
            }
            
            // Fréquences
            if (airport.frequencies && Array.isArray(airport.frequencies) && airport.frequencies.length > 0) {
                html += '<div class="bg-gray-50 p-4 rounded-lg">';
                html += '<h4 class="font-semibold text-gray-800 mb-2">Fréquences radio</h4>';
                airport.frequencies.forEach((freq) => {
                    if (freq.value && freq.name) {
                        html += `<p class="text-sm text-gray-700 ml-4">${freq.name}: ${freq.value} ${freq.unit === 2 ? 'MHz' : ''}</p>`;
                    }
                });
                html += '</div>';
            }
            
            html += '</div>';
            
            content.innerHTML = html;
            
            // Initialiser la carte si les coordonnées sont disponibles
            if (airport.geometry && airport.geometry.coordinates) {
                const coords = airport.geometry.coordinates;
                const lat = coords[1];
                const lon = coords[0];
                
                // Fonction pour initialiser la carte
                const initMap = () => {
                    const mapElement = document.getElementById('aeroport-map');
                    if (!mapElement) return;
                    
                    // Vérifier si Leaflet est déjà chargé
                    if (typeof L === 'undefined') {
                        // Charger Leaflet depuis CDN
                        const leafletCSS = document.createElement('link');
                        leafletCSS.rel = 'stylesheet';
                        leafletCSS.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                        document.head.appendChild(leafletCSS);
                        
                        const leafletScript = document.createElement('script');
                        leafletScript.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                        leafletScript.onload = () => {
                            // Corriger les icônes par défaut de Leaflet
                            delete L.Icon.Default.prototype._getIconUrl;
                            L.Icon.Default.mergeOptions({
                                iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon-2x.png',
                                iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png',
                                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                            });
                            createMap();
                        };
                        document.head.appendChild(leafletScript);
                    } else {
                        createMap();
                    }
                };
                
                const createMap = () => {
                    const mapElement = document.getElementById('aeroport-map');
                    if (!mapElement || typeof L === 'undefined') return;
                    
                    // Nettoyer l'ancienne carte si elle existe
                    if (airportMapInstance) {
                        airportMapInstance.remove();
                        airportMapInstance = null;
                    }
                    
                    // Initialiser la carte centrée sur l'aérodrome
                    const map = L.map('aeroport-map').setView([lat, lon], 9);
                    airportMapInstance = map; // Stocker l'instance
                    
                    // Ajouter la couche OpenStreetMap
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                        maxZoom: 19
                    }).addTo(map);
                    
                    // Ajouter les tuiles OpenAIP si disponibles
                    const openaipLayer = L.tileLayer('/api/openaip/tiles/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openaip.net">openAIP Data (CC-BY-NC)</a>',
                        minZoom: 4,
                        maxZoom: 14,
                        opacity: 0.7
                    });
                    openaipLayer.addTo(map);
                    
                    // Ajouter un marqueur sur l'aérodrome (sans popup)
                    L.marker([lat, lon]).addTo(map);
                };
                
                // Attendre que le DOM soit prêt
                setTimeout(initMap, 100);
            }
        }

        // Variable pour stocker l'instance de la carte
        let airportMapInstance = null;
        let fullscreenMapInstance = null;
        
        function ouvrirCartePleinEcran(lat, lon, airportName, icaoCode) {
            const modal = document.getElementById('modalCartePleinEcran');
            const mapContainer = document.getElementById('carte-plein-ecran');
            
            // Afficher la modal
            modal.classList.remove('hidden');
            
            // Fonction pour initialiser la carte plein écran
            const initFullscreenMap = () => {
                if (!mapContainer) return;
                
                // Vérifier si Leaflet est déjà chargé
                if (typeof L === 'undefined') {
                    // Charger Leaflet si nécessaire
                    const leafletCSS = document.createElement('link');
                    leafletCSS.rel = 'stylesheet';
                    leafletCSS.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                    document.head.appendChild(leafletCSS);
                    
                    const leafletScript = document.createElement('script');
                    leafletScript.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                    leafletScript.onload = () => {
                        delete L.Icon.Default.prototype._getIconUrl;
                        L.Icon.Default.mergeOptions({
                            iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon-2x.png',
                            iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png',
                            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                        });
                        createFullscreenMap();
                    };
                    document.head.appendChild(leafletScript);
                } else {
                    createFullscreenMap();
                }
            };
            
            const createFullscreenMap = () => {
                if (!mapContainer || typeof L === 'undefined') return;
                
                // Nettoyer l'ancienne carte si elle existe
                if (fullscreenMapInstance) {
                    fullscreenMapInstance.remove();
                    fullscreenMapInstance = null;
                }
                
                // Attendre un peu pour que le conteneur soit visible
                setTimeout(() => {
                    // Initialiser la carte centrée sur l'aérodrome
                    const map = L.map('carte-plein-ecran').setView([lat, lon], 9);
                    fullscreenMapInstance = map;
                    
                    // Ajouter la couche OpenStreetMap
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                        maxZoom: 19
                    }).addTo(map);
                    
                    // Ajouter les tuiles OpenAIP si disponibles
                    const openaipLayer = L.tileLayer('/api/openaip/tiles/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openaip.net">openAIP Data (CC-BY-NC)</a>',
                        minZoom: 4,
                        maxZoom: 14,
                        opacity: 0.7
                    });
                    openaipLayer.addTo(map);
                    
                    // Ajouter un marqueur sur l'aérodrome (sans popup)
                    L.marker([lat, lon]).addTo(map);
                    
                    // Invalider la taille de la carte pour qu'elle s'adapte au conteneur
                    setTimeout(() => {
                        map.invalidateSize();
                    }, 200);
                }, 100);
            };
            
            // Attendre que le DOM soit prêt
            setTimeout(initFullscreenMap, 100);
        }
        
        function fermerCartePleinEcran() {
            // Nettoyer la carte si elle existe
            if (fullscreenMapInstance) {
                fullscreenMapInstance.remove();
                fullscreenMapInstance = null;
            }
            document.getElementById('modalCartePleinEcran').classList.add('hidden');
        }

        function fermerModalAeroport() {
            // Nettoyer la carte si elle existe
            if (airportMapInstance) {
                airportMapInstance.remove();
                airportMapInstance = null;
            }
            document.getElementById('modalAeroport').classList.add('hidden');
        }

        // Fermer la modal en cliquant à l'extérieur
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('modalAeroport');
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        fermerModalAeroport();
                    }
                });
            }
        });

        // Fermer avec Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modal = document.getElementById('modalAeroport');
                if (modal && !modal.classList.contains('hidden')) {
                    fermerModalAeroport();
                }
            }
        });
    </script>

    <!-- Modal Détails Aérodrome -->
    <div id="modalAeroport" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-2xl font-bold text-gray-900">Détails de l'aérodrome</h3>
                <button onclick="fermerModalAeroport()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="modalAeroportContent" class="max-h-[70vh] overflow-y-auto">
                <p class="text-center text-gray-500">Chargement...</p>
            </div>
        </div>
    </div>

    <!-- Modal Carte Plein Écran -->
    <div id="modalCartePleinEcran" class="hidden fixed inset-0 bg-gray-900 bg-opacity-95 flex flex-col" style="z-index: 9999;">
        <div class="flex justify-between items-center p-4 bg-gray-800 text-white">
            <h3 class="text-xl font-bold">Carte de l'aérodrome</h3>
            <button onclick="fermerCartePleinEcran()" class="text-white hover:text-gray-300 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div id="carte-plein-ecran" class="flex-1 w-full" style="height: calc(100vh - 64px);"></div>
    </div>
</body>
</html>


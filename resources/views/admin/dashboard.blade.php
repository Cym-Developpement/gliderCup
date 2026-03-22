<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Administration - Wassmer Cup</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Force le curseur crosshair en mode édition (priorité sur leaflet-grab) */
        .carte-mode-edition,
        .carte-mode-edition .leaflet-grab,
        .carte-mode-edition .leaflet-interactive,
        .carte-mode-edition .leaflet-marker-icon,
        .carte-mode-edition .leaflet-tile-pane,
        .carte-mode-edition .leaflet-overlay-pane {
            cursor: crosshair !important;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-sky-50 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-[95vw]">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Administration - Wassmer Cup</h1>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-blue-600 hover:text-blue-800">
                        Déconnexion
                    </button>
                </form>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <!-- Statistiques -->
            <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-8">
                <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded">
                    <p class="text-sm text-gray-600">En attente</p>
                    <p class="text-2xl font-bold text-yellow-700">{{ $inscriptionsEnAttente }}</p>
                </div>
                <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded">
                    <p class="text-sm text-gray-600">Validées</p>
                    <p class="text-2xl font-bold text-green-700">{{ $inscriptionsValidees }}</p>
                </div>
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded">
                    <p class="text-sm text-gray-600">Refusées</p>
                    <p class="text-2xl font-bold text-red-700">{{ $inscriptionsRefusees }}</p>
                </div>
                <div class="bg-orange-50 border-l-4 border-orange-500 p-4 rounded">
                    <p class="text-sm text-gray-600">Paiements en attente</p>
                    <p class="text-2xl font-bold text-orange-700">{{ $paiementsNonValides }}</p>
                </div>
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                    <p class="text-sm text-gray-600">Planeurs</p>
                    <p class="text-2xl font-bold text-blue-700">{{ $totalPlaneurs }}/15</p>
                </div>
                <div class="bg-purple-50 border-l-4 border-purple-500 p-4 rounded">
                    <p class="text-sm text-gray-600">Visiteurs uniques</p>
                    <p class="text-2xl font-bold text-purple-700">{{ $visitesAccueil }}</p>
                    @if(isset($visitesAccueilAujourdhui))
                        <p class="text-xs text-purple-600 mt-1">Aujourd'hui: {{ $visitesAccueilAujourdhui }}</p>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="mb-6 flex justify-end">
                <div class="relative inline-block text-left">
                    <button onclick="toggleDropdownActions()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium flex items-center gap-2">
                        Actions
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    
                    <div id="dropdownActions" class="hidden fixed w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 overflow-y-auto" style="z-index: 9999; max-height: 80vh;">
                        <div class="py-1" role="menu">
                            <a href="{{ route('admin.paiement.test') }}" target="_blank" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                Voir la page de paiement
                            </a>
                            
                            <div class="border-t border-gray-200 my-1"></div>
                            
                            <a href="{{ route('admin.planeurs') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                Voir les planeurs inscrits
                            </a>
                            
                            <a href="{{ route('admin.export.pilotes') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                Exporter liste des pilotes (CSV)
                            </a>
                            
                            <a href="{{ route('admin.export.planeurs') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                Exporter liste des planeurs (CSV)
                            </a>
                            
                            <div class="border-t border-gray-200 my-1"></div>
                            
                            <button onclick="openPaiementModal(); closeDropdownActions();" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                Configuration paiement
                            </button>
                            
                            <button onclick="ouvrirModalReglement(); closeDropdownActions();" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                Gérer le règlement
                            </button>
                            
                            <button onclick="ouvrirModalCodeAeroport(); closeDropdownActions();" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                Configurer le code aérodrome
                            </button>
                            
                            <button onclick="ouvrirModalCartePointsVirage(); closeDropdownActions();" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                Gérer les points de virage
                            </button>

                            <button id="btn-regenerer-carte" onclick="regenererCarte(); closeDropdownActions();" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                Régénérer la carte
                            </button>

                            <button onclick="ouvrirModalMessageGroupe(); closeDropdownActions();" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                Envoyer un message à tous
                            </button>
                            
                            <button onclick="ouvrirTableMessagesGroupes(); closeDropdownActions();" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                Historique des messages
                            </button>

                            <div class="border-t border-gray-200 my-1"></div>

                            <button onclick="ouvrirModalAdmins(); closeDropdownActions();" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                Gérer les administrateurs
                            </button>

                            <div class="border-t border-gray-200 my-1"></div>

                            <a href="{{ route('admin.backup') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" onclick="closeDropdownActions();">
                                Télécharger une sauvegarde
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Liste des inscriptions -->
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Liste des inscriptions</h2>
            <div class="overflow-x-auto" style="overflow-y: visible;">
                <table class="min-w-full bg-white border border-gray-300" style="position: relative;">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 border-b text-left">Nom</th>
                            <th class="px-4 py-2 border-b text-left">Prénom</th>
                            <th class="px-4 py-2 border-b text-left">Email</th>
                            <th class="px-4 py-2 border-b text-left">Qualité</th>
                            <th class="px-4 py-2 border-b text-left">Statut</th>
                            <th class="px-4 py-2 border-b text-left">Paiement</th>
                            <th class="px-4 py-2 border-b text-left">Planeurs</th>
                            <th class="px-4 py-2 border-b text-left text-right">Prix facturé</th>
                            <th class="px-4 py-2 border-b text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inscriptions as $inscription)
                            <tr>
                                <td class="px-4 py-2 border-b">{{ $inscription->nom }}</td>
                                <td class="px-4 py-2 border-b">{{ $inscription->prenom }}</td>
                                <td class="px-4 py-2 border-b">{{ $inscription->email }}</td>
                                <td class="px-4 py-2 border-b">{{ $inscription->qualite }}</td>
                                <td class="px-4 py-2 border-b">
                                    @if($inscription->statut === 'en_attente')
                                        <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-sm">En attente</span>
                                    @elseif($inscription->statut === 'validee')
                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm">Validée</span>
                                    @elseif($inscription->statut === 'refusee')
                                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-sm">Refusée</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 border-b">
                                    @if($inscription->paiement_valide)
                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm">Validé</span>
                                    @else
                                        <span class="bg-orange-100 text-orange-800 px-2 py-1 rounded text-sm">⏳ En attente</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 border-b">
                                    {{ $inscription->planeurs->count() }}
                                </td>
                                <td class="px-4 py-2 border-b text-right font-semibold {{ $inscription->montant_custom !== null ? 'text-blue-600' : '' }}">
                                    @php
                                        $montantPlaneur = 50;
                                        $montantAdhesion = 50;
                                        $nombrePlaneurs = $inscription->planeurs->count();
                                        $prixTotal = $inscription->montant_custom !== null ? $inscription->montant_custom : ($nombrePlaneurs * $montantPlaneur) + $montantAdhesion;
                                    @endphp
                                    {{ number_format($prixTotal, 2, ',', ' ') }} €
                                </td>
                                <td class="px-4 py-2 border-b relative" style="overflow: visible;">
                                    <div class="relative inline-block text-left">
                                        <button onclick="toggleDropdown({{ $inscription->id }})" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium flex items-center gap-2">
                                            Actions
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>
                                        
                                        <div id="dropdown-{{ $inscription->id }}" class="hidden fixed w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5" style="z-index: 9999;">
                                            <div class="py-1" role="menu">
                                                <button onclick="voirDetails({{ $inscription->id }}); closeDropdown({{ $inscription->id }});" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                                    Voir détails
                                                </button>
                                                
                                                <button onclick="ouvrirMessagerie({{ $inscription->id }}); closeDropdown({{ $inscription->id }});" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                                    Envoyer un message
                                                </button>
                                                
                                                <form action="{{ route('admin.pilotes.envoyer-message-compte-cree', $inscription->id) }}" method="POST" class="inline w-full" onsubmit="closeDropdown({{ $inscription->id }});">
                                                    @csrf
                                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                                        Envoyer message compte créé
                                                    </button>
                                                </form>
                                                
                                                <button onclick="copierLienPaiement('{{ $inscription->identifiant_virement ?? $inscription->id }}'); closeDropdown({{ $inscription->id }});" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                                    Copier lien paiement
                                                </button>
                                                
                                                <form action="{{ route('admin.paiement.envoyer-lien', $inscription->id) }}" method="POST" class="inline w-full" onsubmit="closeDropdown({{ $inscription->id }});">
                                                    @csrf
                                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                                        Envoyer lien paiement
                                                    </button>
                                                </form>
                                                
                                                <div class="border-t border-gray-200 my-1"></div>
                                                
                                                @if($inscription->statut === 'en_attente')
                                                    <form action="{{ route('admin.inscriptions.valider', $inscription->id) }}" method="POST" class="inline w-full" onsubmit="closeDropdown({{ $inscription->id }});">
                                                        @csrf
                                                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-green-700 hover:bg-green-50" role="menuitem">
                                                            Valider inscription
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('admin.inscriptions.refuser', $inscription->id) }}" method="POST" class="inline w-full" onsubmit="closeDropdown({{ $inscription->id }});">
                                                        @csrf
                                                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-50" role="menuitem">
                                                            Refuser inscription
                                                        </button>
                                                    </form>
                                                @endif
                                                
                                                @if($inscription->statut === 'refusee')
                                                    <div class="border-t border-gray-200 my-1"></div>
                                                    <form action="{{ route('admin.inscriptions.supprimer', $inscription->id) }}" method="POST" class="inline w-full" onsubmit="return confirmSuppression({{ $inscription->id }});">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-50 font-semibold" role="menuitem">
                                                            Supprimer complètement
                                                        </button>
                                                    </form>
                                                @endif
                                                
                                                @if(!$inscription->paiement_valide)
                                                    <form action="{{ route('admin.paiement.valider', $inscription->id) }}" method="POST" class="inline w-full" onsubmit="closeDropdown({{ $inscription->id }});">
                                                        @csrf
                                                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-green-700 hover:bg-green-50" role="menuitem">
                                                            Valider paiement
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('admin.paiement.invalider', $inscription->id) }}" method="POST" class="inline w-full" onsubmit="closeDropdown({{ $inscription->id }});">
                                                        @csrf
                                                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-orange-700 hover:bg-orange-50" role="menuitem" onclick="return confirm('Êtes-vous sûr de vouloir invalider ce paiement ?')">
                                                            Invalider paiement
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-4 text-center text-gray-500">Aucune inscription</td>
                            </tr>
                        @endforelse
                        @if($inscriptions->count() > 0)
                            <tr class="bg-gray-50 font-bold">
                                <td colspan="7" class="px-4 py-3 border-t-2 border-gray-400 text-right">Total :</td>
                                <td class="px-4 py-3 border-t-2 border-gray-400 text-right">
                                    @php
                                        $montantPlaneur = 50;
                                        $montantAdhesion = 50;
                                        $totalGeneral = 0;
                                        foreach($inscriptions as $inscription) {
                                            $nombrePlaneurs = $inscription->planeurs->count();
                                            $prixTotal = $inscription->montant_custom !== null ? $inscription->montant_custom : ($nombrePlaneurs * $montantPlaneur) + $montantAdhesion;
                                            $totalGeneral += $prixTotal;
                                        }
                                    @endphp
                                    {{ number_format($totalGeneral, 2, ',', ' ') }} €
                                </td>
                                <td class="px-4 py-3 border-t-2 border-gray-400"></td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($inscriptions->hasPages())
                <div class="mt-6">
                    {{ $inscriptions->links() }}
                </div>
            @endif

            <!-- Liste des planeurs inscrits -->
            <div class="mt-8">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-semibold text-gray-800">Planeurs inscrits</h2>
                    <span class="text-sm text-gray-600">{{ $planeursInscrits->count() }} / {{ $limitePlaneurs }} planeurs</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-300">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 border-b text-left">Immatriculation</th>
                                <th class="px-4 py-2 border-b text-left">Marque</th>
                                <th class="px-4 py-2 border-b text-left">Modèle</th>
                                <th class="px-4 py-2 border-b text-left">Type</th>
                                <th class="px-4 py-2 border-b text-left">Propriétaire</th>
                                <th class="px-4 py-2 border-b text-left">Pilotes inscrits</th>
                                <th class="px-4 py-2 border-b text-left">Statut</th>
                                <th class="px-4 py-2 border-b text-left">Date d'inscription</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($planeursInscrits as $planeur)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 border-b font-semibold">{{ $planeur->immatriculation }}</td>
                                    <td class="px-4 py-2 border-b">{{ $planeur->marque ?? '-' }}</td>
                                    <td class="px-4 py-2 border-b">{{ $planeur->modele }}</td>
                                    <td class="px-4 py-2 border-b">{{ $planeur->type ? ucfirst($planeur->type) : '-' }}</td>
                                    <td class="px-4 py-2 border-b">
                                        @if($planeur->piloteProprietaire)
                                            <p class="font-semibold">{{ $planeur->piloteProprietaire->prenom }} {{ $planeur->piloteProprietaire->nom }}</p>
                                            <p class="text-sm text-gray-600">{{ $planeur->piloteProprietaire->email }}</p>
                                        @else
                                            <span class="text-gray-400">Non défini</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 border-b">
                                        @if($planeur->pilotes->count() > 0)
                                            <div class="space-y-1">
                                                @foreach($planeur->pilotes as $pilote)
                                                    <div class="text-sm">
                                                        <span class="font-medium">{{ $pilote->prenom }} {{ $pilote->nom }}</span>
                                                        @if($pilote->id === $planeur->pilote_id)
                                                            <span class="text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded ml-1">Propriétaire</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-gray-400">Aucun pilote inscrit</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 border-b">
                                        @if($planeur->statut === 'en_attente')
                                            <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-sm">En attente</span>
                                        @elseif($planeur->statut === 'validee')
                                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm">Validé</span>
                                        @elseif($planeur->statut === 'refusee')
                                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-sm">Refusé</span>
                                        @else
                                            <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-sm">{{ $planeur->statut ?? 'N/A' }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 border-b text-sm text-gray-600">
                                        {{ $planeur->created_at->format('d/m/Y H:i') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-4 text-center text-gray-500">Aucun planeur inscrit</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Table des paiements HelloAsso -->
            <div class="mt-8">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-semibold text-gray-800">Paiements HelloAsso</h2>
                    <button onclick="verifierPaiements()" id="btnVerifierPaiements" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <span id="btnVerifierPaiementsText">Vérifier les paiements</span>
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-300">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 border-b text-left">Nom</th>
                                <th class="px-4 py-2 border-b text-left">Prénom</th>
                                <th class="px-4 py-2 border-b text-left">Checkout Intent ID</th>
                                <th class="px-4 py-2 border-b text-left">Date de création</th>
                                <th class="px-4 py-2 border-b text-left">Montant</th>
                                <th class="px-4 py-2 border-b text-left">Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($paiementsHelloAsso as $paiement)
                                <tr id="paiement-row-{{ $paiement->id }}" data-checkout-id="{{ trim($paiement->helloasso_checkout_intent_id ?? '') }}">
                                    <td class="px-4 py-2 border-b">
                                        <button onclick="voirDetails({{ $paiement->id }})" class="text-blue-600 hover:text-blue-800 hover:underline cursor-pointer">{{ $paiement->nom }}</button>
                                    </td>
                                    <td class="px-4 py-2 border-b">
                                        <button onclick="voirDetails({{ $paiement->id }})" class="text-blue-600 hover:text-blue-800 hover:underline cursor-pointer">{{ $paiement->prenom }}</button>
                                    </td>
                                    <td class="px-4 py-2 border-b">
                                        @php
                                            $checkoutId = trim($paiement->helloasso_checkout_intent_id ?? '');
                                        @endphp
                                        @if(!empty($checkoutId))
                                            <span class="font-mono text-sm" title="{{ $checkoutId }}">{{ $checkoutId }}</span>
                                        @else
                                            <span class="text-gray-400 italic">Non défini</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 border-b">{{ $paiement->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-2 border-b text-right font-semibold {{ $paiement->montant_custom !== null ? 'text-blue-600' : '' }}">
                                        @php
                                            $montantPlaneur = 50;
                                            $montantAdhesion = 50;
                                            $nombrePlaneurs = $paiement->planeurs->count();
                                            $prixTotal = $paiement->montant_custom !== null ? $paiement->montant_custom : ($nombrePlaneurs * $montantPlaneur) + $montantAdhesion;
                                        @endphp
                                        {{ number_format($prixTotal, 2, ',', ' ') }} €
                                    </td>
                                    <td class="px-4 py-2 border-b">
                                        @if($paiement->paiement_valide)
                                            <span class="px-2 py-1 rounded text-sm font-medium bg-green-100 text-green-800">
                                                ✓ Paiement validé
                                            </span>
                                        @else
                                            <span id="statut-{{ $paiement->id }}" class="px-2 py-1 rounded text-sm font-medium bg-gray-100 text-gray-600">
                                                <span class="inline-block w-2 h-2 rounded-full bg-gray-400 mr-1 animate-pulse"></span>
                                                Vérification...
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-4 text-center text-gray-500">Aucun paiement HelloAsso</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Table des messages de contact -->
            <div class="mt-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Messages de contact</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-300">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 border-b text-left">Date</th>
                                <th class="px-4 py-2 border-b text-left">Nom</th>
                                <th class="px-4 py-2 border-b text-left">Email</th>
                                <th class="px-4 py-2 border-b text-left">Message</th>
                                <th class="px-4 py-2 border-b text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($messagesContact as $message)
                                <tr>
                                    <td class="px-4 py-2 border-b">{{ $message->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-2 border-b">{{ $message->nom }}</td>
                                    <td class="px-4 py-2 border-b">
                                        <a href="mailto:{{ $message->email }}" class="text-blue-600 hover:text-blue-800 hover:underline">
                                            {{ $message->email }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-2 border-b">
                                        <div class="max-w-md truncate" title="{{ $message->message }}">
                                            {{ Str::limit($message->message, 100) }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-2 border-b">
                                        <button onclick="ouvrirModalReponseContact({{ $message->id }}, '{{ $message->nom }}', '{{ $message->email }}', `{{ addslashes($message->message) }}`)" 
                                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium">
                                            Répondre
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-4 text-center text-gray-500">Aucun message de contact en attente</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Todo list -->
            <div class="mt-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Todo list</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-300">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 border-b text-left">Intitulé</th>
                                <th class="px-4 py-2 border-b text-left">Personne</th>
                                <th class="px-4 py-2 border-b text-left w-40">Statut</th>
                                <th class="px-4 py-2 border-b text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tachesBody">
                            <tr>
                                <td colspan="4" class="px-4 py-4 text-center text-gray-500">Chargement...</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50">
                                <td class="px-4 py-2 border-t">
                                    <input type="text" id="newTacheIntitule" placeholder="Intitulé" class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </td>
                                <td class="px-4 py-2 border-t">
                                    <input type="text" id="newTachePersonne" placeholder="Personne" class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </td>
                                <td class="px-4 py-2 border-t"></td>
                                <td class="px-4 py-2 border-t">
                                    <button onclick="ajouterTache()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1 rounded text-sm font-medium">
                                        Ajouter
                                    </button>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal pour répondre aux messages de contact -->
    <div id="modalReponseContact" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-2xl font-bold text-gray-900">Répondre au message de contact</h3>
                    <button onclick="fermerModalReponseContact()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form id="formReponseContact" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">De :</label>
                        <div class="px-4 py-2 bg-gray-50 rounded-lg">
                            <span id="contact_nom_display" class="font-semibold"></span>
                            <span id="contact_email_display" class="text-gray-600 ml-2"></span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Message original :</label>
                        <div class="px-4 py-2 bg-gray-50 rounded-lg max-h-32 overflow-y-auto">
                            <p id="contact_message_display" class="text-sm text-gray-700 whitespace-pre-wrap"></p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="contact_reponse" class="block text-sm font-medium text-gray-700 mb-2">
                            Votre réponse <span class="text-red-500">*</span>
                        </label>
                        <textarea id="contact_reponse" name="reponse" required rows="6"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Tapez votre réponse ici..."></textarea>
                        @error('reponse')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-4">
                        <button type="button" onclick="fermerModalReponseContact()"
                            class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
                            Annuler
                        </button>
                        <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
                            Envoyer la réponse
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal pour afficher les détails du pilote -->
    <div id="modalPilote" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <!-- En-tête de la modal -->
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-2xl font-bold text-gray-900">Détails du pilote</h3>
                    <button onclick="fermerModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Contenu de la modal -->
                <div id="modalContent" class="max-h-[70vh] overflow-y-auto">
                    <p class="text-center text-gray-500">Chargement...</p>
                </div>
            </div>
        </div>
    </div>

    <style>
        #modalPilote {
            transition: opacity 0.3s ease;
        }
    </style>

    <script>
        // Vérifier tous les paiements HelloAsso
        async function verifierPaiements() {
            const btn = document.getElementById('btnVerifierPaiements');
            const btnText = document.getElementById('btnVerifierPaiementsText');
            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');
            btnText.textContent = 'Vérification en cours...';

            try {
                const response = await fetch('{{ route("admin.paiement.verifier-tous") }}');
                const data = await response.json();

                if (data.success) {
                    btnText.textContent = `${data.resultats.valides} validé(s), ${data.resultats.en_attente} en attente`;
                    if (data.resultats.valides > 0) {
                        // Recharger la page pour mettre à jour les statuts
                        setTimeout(() => location.reload(), 1500);
                    }
                } else {
                    btnText.textContent = 'Erreur lors de la vérification';
                }
            } catch (e) {
                btnText.textContent = 'Erreur réseau';
            } finally {
                setTimeout(() => {
                    btn.disabled = false;
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                    btnText.textContent = 'Vérifier les paiements';
                }, 5000);
            }
        }

        // Récupérer le token CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

        function voirDetails(piloteId) {
            const modal = document.getElementById('modalPilote');
            const content = document.getElementById('modalContent');
            
            // Afficher la modal
            modal.classList.remove('hidden');
            content.innerHTML = '<p class="text-center text-gray-500">Chargement...</p>';

            // Récupérer les détails du pilote
            fetch(`/admin/pilotes/${piloteId}/details`)
                .then(response => response.json())
                .then(data => {
                    const pilote = data.pilote;
                    const planeurs = data.planeurs;

                    // Convertir date_naissance dd/mm/yyyy en yyyy-mm-dd pour l'input date
                    const dateNaissanceParts = pilote.date_naissance ? pilote.date_naissance.split('/') : [];
                    const dateNaissanceISO = dateNaissanceParts.length === 3 ? `${dateNaissanceParts[2]}-${dateNaissanceParts[1]}-${dateNaissanceParts[0]}` : '';

                    let html = `
                        <div class="space-y-6">
                            <!-- Informations personnelles (éditables) -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="text-lg font-semibold text-gray-800 mb-3">Informations personnelles</h4>
                                <div id="piloteInfoFeedback" class="hidden mb-3 px-4 py-2 rounded text-sm"></div>
                                <form id="formPiloteInfo" data-pilote-id="${pilote.id}">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="text-sm text-gray-600">Nom</label>
                                            <input type="text" name="nom" value="${pilote.nom}" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        <div>
                                            <label class="text-sm text-gray-600">Prénom</label>
                                            <input type="text" name="prenom" value="${pilote.prenom}" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        <div>
                                            <label class="text-sm text-gray-600">Qualité</label>
                                            <input type="text" name="qualite" value="${pilote.qualite}" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        <div>
                                            <label class="text-sm text-gray-600">Date de naissance</label>
                                            <input type="date" name="date_naissance" value="${dateNaissanceISO}" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        <div>
                                            <label class="text-sm text-gray-600">Email</label>
                                            <input type="email" name="email" value="${pilote.email}" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        <div>
                                            <label class="text-sm text-gray-600">Téléphone</label>
                                            <input type="text" name="telephone" value="${pilote.telephone || ''}" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        <div>
                                            <label class="text-sm text-gray-600">Club</label>
                                            <input type="text" name="club" value="${pilote.club || ''}" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        <div>
                                            <label class="text-sm text-gray-600">N° FFVP</label>
                                            <input type="text" name="numero_ffvp" value="${pilote.numero_ffvp || ''}" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        <div>
                                            <label class="text-sm text-gray-600">Adresse</label>
                                            <input type="text" name="adresse" value="${pilote.adresse || ''}" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        <div>
                                            <label class="text-sm text-gray-600">Code postal</label>
                                            <input type="text" name="code_postal" value="${pilote.code_postal || ''}" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        <div>
                                            <label class="text-sm text-gray-600">Ville</label>
                                            <input type="text" name="ville" value="${pilote.ville || ''}" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        <div>
                                            <label class="text-sm text-gray-600">Statut</label>
                                            <p class="font-semibold mt-1">
                                                ${pilote.statut === 'en_attente' ? '<span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-sm">En attente</span>' : ''}
                                                ${pilote.statut === 'validee' ? '<span class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm">Validée</span>' : ''}
                                                ${pilote.statut === 'refusee' ? '<span class="bg-red-100 text-red-800 px-2 py-1 rounded text-sm">Refusée</span>' : ''}
                                            </p>
                                        </div>
                                        <div>
                                            <label class="text-sm text-gray-600">Date d'inscription</label>
                                            <p class="font-semibold mt-1">${pilote.created_at}</p>
                                        </div>
                                    </div>
                                    <div class="mt-4 flex justify-end">
                                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md text-sm font-medium transition duration-200">
                                            Enregistrer les informations
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Informations de paiement -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="text-lg font-semibold text-gray-800 mb-3">Informations de paiement</h4>
                                <div id="montantFeedback" class="hidden mb-3 px-4 py-2 rounded text-sm"></div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-600">Statut du paiement</p>
                                        <p class="font-semibold">
                                            ${pilote.paiement_valide ? '<span class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm">Validé</span>' : '<span class="bg-orange-100 text-orange-800 px-2 py-1 rounded text-sm">En attente</span>'}
                                        </p>
                                    </div>
                                    ${pilote.identifiant_virement ? `
                                    <div>
                                        <p class="text-sm text-gray-600">Identifiant de paiement</p>
                                        <p class="font-semibold font-mono text-sm break-all">${pilote.identifiant_virement}</p>
                                    </div>
                                    ` : ''}
                                    ${pilote.helloasso_checkout_intent_id ? `
                                    <div>
                                        <p class="text-sm text-gray-600">HelloAsso Checkout Intent ID</p>
                                        <p class="font-semibold font-mono text-sm break-all">${pilote.helloasso_checkout_intent_id}</p>
                                    </div>
                                    ` : ''}
                                    ${!pilote.paiement_valide ? `
                                    <div class="md:col-span-2">
                                        <form id="formMontantCustom" data-pilote-id="${pilote.id}" class="flex items-end gap-3">
                                            <div class="flex-1">
                                                <label class="text-sm text-gray-600">Montant facturé personnalisé (€)</label>
                                                <input type="number" name="montant_custom" step="0.01" min="0" value="${pilote.montant_custom !== null ? pilote.montant_custom : ''}" placeholder="Laisser vide = calcul auto" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            </div>
                                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-200">
                                                Enregistrer
                                            </button>
                                        </form>
                                        <p class="text-xs text-gray-500 mt-1">Si vide, le calcul standard s'applique (nb planeurs × 50 € + 50 € adhésion).</p>
                                    </div>
                                    ` : (pilote.montant_custom !== null ? `
                                    <div>
                                        <p class="text-sm text-gray-600">Montant personnalisé</p>
                                        <p class="font-semibold">${parseFloat(pilote.montant_custom).toFixed(2).replace('.', ',')} €</p>
                                    </div>
                                    ` : '')}
                                </div>
                            </div>

                            <!-- Documents du pilote -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="text-lg font-semibold text-gray-800 mb-3">Documents du pilote</h4>
                                <div class="space-y-3">
                                    <!-- Autorisation parentale (uniquement si mineur) -->
                                    ${pilote.est_mineur ? `
                                    <div class="bg-white p-3 rounded border border-gray-200">
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="text-sm font-medium">Autorisation parentale</span>
                                            ${pilote.documents.autorisation_parentale ? `
                                            <a href="/storage/${pilote.documents.autorisation_parentale}" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">
                                                Télécharger
                                            </a>
                                            ` : '<span class="text-red-600 text-xs">Manquant</span>'}
                                        </div>
                                        <form action="/admin/pilotes/${pilote.id}/remplacer-document" method="POST" enctype="multipart/form-data" class="mt-2">
                                            <input type="hidden" name="_token" value="${csrfToken}">
                                            <input type="hidden" name="type_document" value="autorisation_parentale">
                                            <div class="flex gap-2">
                                                <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" required class="flex-1 text-sm border border-gray-300 rounded px-2 py-1">
                                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm">
                                                    ${pilote.documents.autorisation_parentale ? 'Remplacer' : 'Ajouter'}
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                    ` : ''}

                                    <!-- Feuille déclarative qualifications -->
                                    <div class="bg-white p-3 rounded border border-gray-200">
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="text-sm font-medium">Feuille déclarative qualifications</span>
                                            ${pilote.documents.feuille_declarative_qualifications ? `
                                            <a href="/storage/${pilote.documents.feuille_declarative_qualifications}" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">
                                                Télécharger
                                            </a>
                                            ` : '<span class="text-red-600 text-xs">Manquant</span>'}
                                        </div>
                                        <form action="/admin/pilotes/${pilote.id}/remplacer-document" method="POST" enctype="multipart/form-data" class="mt-2">
                                            <input type="hidden" name="type_document" value="feuille_declarative_qualifications">
                                            <div class="flex gap-2">
                                                <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" required class="flex-1 text-sm border border-gray-300 rounded px-2 py-1">
                                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm">
                                                    ${pilote.documents.feuille_declarative_qualifications ? 'Remplacer' : 'Ajouter'}
                                                </button>
                                            </div>
                                        </form>
                                    </div>

                                    <!-- Visite médicale classe 2 -->
                                    <div class="bg-white p-3 rounded border border-gray-200">
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="text-sm font-medium">Visite médicale classe 2</span>
                                            ${pilote.documents.visite_medicale_classe_2 ? `
                                            <a href="/storage/${pilote.documents.visite_medicale_classe_2}" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">
                                                Télécharger
                                            </a>
                                            ` : '<span class="text-red-600 text-xs">Manquant</span>'}
                                        </div>
                                        <form action="/admin/pilotes/${pilote.id}/remplacer-document" method="POST" enctype="multipart/form-data" class="mt-2">
                                            <input type="hidden" name="type_document" value="visite_medicale_classe_2">
                                            <div class="flex gap-2">
                                                <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" required class="flex-1 text-sm border border-gray-300 rounded px-2 py-1">
                                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm">
                                                    ${pilote.documents.visite_medicale_classe_2 ? 'Remplacer' : 'Ajouter'}
                                                </button>
                                            </div>
                                        </form>
                                    </div>

                                    <!-- SPL Valide -->
                                    <div class="bg-white p-3 rounded border border-gray-200">
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="text-sm font-medium">SPL Valide</span>
                                            ${pilote.documents.spl_valide ? `
                                            <a href="/storage/${pilote.documents.spl_valide}" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">
                                                Télécharger
                                            </a>
                                            ` : '<span class="text-red-600 text-xs">Manquant</span>'}
                                        </div>
                                        <form action="/admin/pilotes/${pilote.id}/remplacer-document" method="POST" enctype="multipart/form-data" class="mt-2">
                                            <input type="hidden" name="type_document" value="spl_valide">
                                            <div class="flex gap-2">
                                                <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" required class="flex-1 text-sm border border-gray-300 rounded px-2 py-1">
                                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm">
                                                    ${pilote.documents.spl_valide ? 'Remplacer' : 'Ajouter'}
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Planeurs -->
                            ${planeurs.length > 0 ? `
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="text-lg font-semibold text-gray-800 mb-3">Planeurs (${planeurs.length})</h4>
                                ${planeurs.map((planeur, index) => `
                                    <div class="mb-4 p-3 bg-white rounded-lg">
                                        <h5 class="font-semibold mb-2">Planeur ${index + 1}</h5>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                                            ${planeur.marque ? `
                                            <div>
                                                <p class="text-sm text-gray-600">Marque</p>
                                                <p class="font-semibold">${planeur.marque}</p>
                                            </div>
                                            ` : ''}
                                            <div>
                                                <p class="text-sm text-gray-600">Modèle</p>
                                                <p class="font-semibold">${planeur.modele}</p>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-600">Immatriculation</p>
                                                <p class="font-semibold">${planeur.immatriculation}</p>
                                            </div>
                                            ${planeur.type ? `
                                            <div>
                                                <p class="text-sm text-gray-600">Type</p>
                                                <p class="font-semibold">${planeur.type}</p>
                                            </div>
                                            ` : ''}
                                            ${planeur.proprietaire ? `
                                            <div>
                                                <p class="text-sm text-gray-600">Propriétaire</p>
                                                <p class="font-semibold">${planeur.proprietaire}</p>
                                            </div>
                                            ` : ''}
                                        </div>
                                        <div class="mt-3 pt-3 border-t">
                                            <p class="text-sm font-semibold mb-2">Documents du planeur</p>
                                            <div class="space-y-2">
                                                ${planeur.documents.cdn_cen ? `
                                                <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                                    <span class="text-sm">CDN / CEN</span>
                                                    <a href="/storage/${planeur.documents.cdn_cen}" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">
                                                        Télécharger
                                                    </a>
                                                </div>
                                                ` : '<p class="text-sm text-gray-500">Aucun CDN/CEN</p>'}
                                                ${planeur.documents.responsabilite_civile ? `
                                                <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                                    <span class="text-sm">Responsabilité civile</span>
                                                    <a href="/storage/${planeur.documents.responsabilite_civile}" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">
                                                        Télécharger
                                                    </a>
                                                </div>
                                                ` : '<p class="text-sm text-gray-500">Aucune responsabilité civile</p>'}
                                            </div>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                            ` : '<div class="bg-gray-50 p-4 rounded-lg"><p class="text-sm text-gray-500">Aucun planeur inscrit</p></div>'}
                        </div>
                    `;

                    content.innerHTML = html;

                    // Bind form submit: informations personnelles
                    const formInfo = document.getElementById('formPiloteInfo');
                    if (formInfo) {
                        formInfo.addEventListener('submit', function(e) {
                            e.preventDefault();
                            const id = this.dataset.piloteId;
                            const formData = new FormData(this);
                            const data = Object.fromEntries(formData.entries());
                            const feedback = document.getElementById('piloteInfoFeedback');

                            fetch(`/admin/pilotes/${id}/update`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify(data),
                            })
                            .then(r => r.json())
                            .then(result => {
                                feedback.classList.remove('hidden', 'bg-red-100', 'text-red-800');
                                if (result.success) {
                                    feedback.classList.add('bg-green-100', 'text-green-800');
                                    feedback.textContent = result.message;
                                } else {
                                    feedback.classList.add('bg-red-100', 'text-red-800');
                                    feedback.textContent = result.message || 'Erreur lors de la mise à jour.';
                                }
                                setTimeout(() => feedback.classList.add('hidden'), 3000);
                            })
                            .catch(() => {
                                feedback.classList.remove('hidden', 'bg-green-100', 'text-green-800');
                                feedback.classList.add('bg-red-100', 'text-red-800');
                                feedback.textContent = 'Erreur réseau lors de la mise à jour.';
                                setTimeout(() => feedback.classList.add('hidden'), 3000);
                            });
                        });
                    }

                    // Bind form submit: montant personnalisé
                    const formMontant = document.getElementById('formMontantCustom');
                    if (formMontant) {
                        formMontant.addEventListener('submit', function(e) {
                            e.preventDefault();
                            const id = this.dataset.piloteId;
                            const montantInput = this.querySelector('input[name="montant_custom"]');
                            const montantValue = montantInput.value === '' ? null : parseFloat(montantInput.value);
                            const feedback = document.getElementById('montantFeedback');

                            fetch(`/admin/pilotes/${id}/update-montant`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({ montant_custom: montantValue }),
                            })
                            .then(r => r.json())
                            .then(result => {
                                feedback.classList.remove('hidden', 'bg-red-100', 'text-red-800');
                                if (result.success) {
                                    feedback.classList.add('bg-green-100', 'text-green-800');
                                    feedback.textContent = result.message;
                                } else {
                                    feedback.classList.add('bg-red-100', 'text-red-800');
                                    feedback.textContent = result.message || 'Erreur lors de la mise à jour.';
                                }
                                setTimeout(() => feedback.classList.add('hidden'), 3000);
                            })
                            .catch(() => {
                                feedback.classList.remove('hidden', 'bg-green-100', 'text-green-800');
                                feedback.classList.add('bg-red-100', 'text-red-800');
                                feedback.textContent = 'Erreur réseau lors de la mise à jour.';
                                setTimeout(() => feedback.classList.add('hidden'), 3000);
                            });
                        });
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    content.innerHTML = '<p class="text-center text-red-500">Erreur lors du chargement des détails.</p>';
                });
        }

        function fermerModal() {
            document.getElementById('modalPilote').classList.add('hidden');
        }

        // Fermer la modal en cliquant en dehors
        document.getElementById('modalPilote').addEventListener('click', function(e) {
            if (e.target === this) {
                fermerModal();
            }
        });

        // Fermer la modal avec la touche Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                fermerModal();
            }
        });
    </script>

    <!-- Modal Configuration Paiement -->
    <div id="modalPaiement" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-2xl font-bold text-gray-900">Configuration des paiements</h3>
                <button onclick="closePaiementModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="formPaiementConfig" method="POST" action="{{ route('admin.paiement.configuration.update') }}">
                @csrf
                
                <div class="space-y-4">
                    <!-- Adresse pour chèque -->
                    <div>
                        <label for="adresse_cheque" class="block text-sm font-medium text-gray-700 mb-2">
                            Adresse de réception des chèques
                        </label>
                        <textarea 
                            id="adresse_cheque" 
                            name="adresse_cheque" 
                            rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Association Wassmer Cup&#10;123 Rue de l'Aviation&#10;79000 Niort"></textarea>
                        <p class="text-xs text-gray-500 mt-1">Adresse complète où envoyer les chèques</p>
                    </div>

                    <!-- IBAN -->
                    <div>
                        <label for="iban_virement" class="block text-sm font-medium text-gray-700 mb-2">
                            IBAN pour virement
                        </label>
                        <input 
                            type="text" 
                            id="iban_virement" 
                            name="iban_virement" 
                            maxlength="34"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono"
                            placeholder="FR76 XXXX XXXX XXXX XXXX XXXX XXX">
                        <p class="text-xs text-gray-500 mt-1">IBAN complet (34 caractères max)</p>
                    </div>

                    <!-- BIC -->
                    <div>
                        <label for="bic_virement" class="block text-sm font-medium text-gray-700 mb-2">
                            BIC pour virement
                        </label>
                        <input 
                            type="text" 
                            id="bic_virement" 
                            name="bic_virement" 
                            maxlength="11"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono uppercase"
                            placeholder="XXXXXXXXXXX">
                        <p class="text-xs text-gray-500 mt-1">Code BIC (11 caractères max)</p>
                    </div>

                </div>

                <div class="flex justify-end gap-4 mt-6">
                    <button 
                        type="button" 
                        onclick="closePaiementModal()" 
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-6 rounded-lg transition">
                        Annuler
                    </button>
                    <button 
                        type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition">
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openPaiementModal() {
            // Charger la configuration actuelle
            fetch('{{ route("admin.paiement.configuration") }}')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('adresse_cheque').value = data.adresse_cheque || '';
                    document.getElementById('iban_virement').value = data.iban_virement || '';
                    document.getElementById('bic_virement').value = data.bic_virement || '';
                    document.getElementById('modalPaiement').classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Erreur lors du chargement de la configuration:', error);
                    document.getElementById('modalPaiement').classList.remove('hidden');
                });
        }

        function closePaiementModal() {
            document.getElementById('modalPaiement').classList.add('hidden');
        }

        // Fermer la modal en cliquant à l'extérieur
        document.getElementById('modalPaiement').addEventListener('click', function(e) {
            if (e.target === this) {
                closePaiementModal();
            }
        });

        // Fermer avec Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !document.getElementById('modalPaiement').classList.contains('hidden')) {
                closePaiementModal();
            }
        });

        // Fonction pour copier le lien de paiement
        function copierLienPaiement(identifiant) {
            const lien = window.location.origin + '/paiement/' + identifiant;
            
            // Copier dans le presse-papier
            navigator.clipboard.writeText(lien).then(function() {
                // Afficher un message de confirmation temporaire
                const button = event.target;
                const texteOriginal = button.textContent;
                button.textContent = '✓ Lien copié !';
                button.classList.add('text-green-600');
                
                setTimeout(function() {
                    button.textContent = texteOriginal;
                    button.classList.remove('text-green-600');
                }, 2000);
            }).catch(function(err) {
                // Fallback : afficher le lien dans une alerte
                alert('Lien de paiement :\n' + lien + '\n\n(Copiez ce lien manuellement)');
            });
        }

        // Fonction pour gérer l'ouverture/fermeture des dropdowns
        function toggleDropdown(id) {
            const dropdown = document.getElementById('dropdown-' + id);
            const button = event.target.closest('button');
            const isHidden = dropdown.classList.contains('hidden');

            // Fermer tous les autres dropdowns
            document.querySelectorAll('[id^="dropdown-"]').forEach(function(d) {
                if (d.id !== 'dropdown-' + id) {
                    d.classList.add('hidden');
                }
            });

            if (isHidden && button) {
                // Position fixed = coordonnées viewport (sans scrollY)
                const rect = button.getBoundingClientRect();
                const dropdownHeight = dropdown.scrollHeight || 300;
                const spaceBelow = window.innerHeight - rect.bottom;

                // Ouvrir vers le haut si pas assez de place en bas
                if (spaceBelow < dropdownHeight && rect.top > dropdownHeight) {
                    dropdown.style.top = '';
                    dropdown.style.bottom = (window.innerHeight - rect.top + 4) + 'px';
                } else {
                    dropdown.style.bottom = '';
                    dropdown.style.top = (rect.bottom + 4) + 'px';
                }
                dropdown.style.right = (window.innerWidth - rect.right) + 'px';
                dropdown.style.left = '';
            }

            // Toggle le dropdown actuel
            if (isHidden) {
                dropdown.classList.remove('hidden');
            } else {
                dropdown.classList.add('hidden');
            }
        }

        function closeDropdown(id) {
            const dropdown = document.getElementById('dropdown-' + id);
            if (dropdown) {
                dropdown.classList.add('hidden');
            }
        }

        function toggleDropdownActions() {
            const dropdown = document.getElementById('dropdownActions');
            const isHidden = dropdown.classList.contains('hidden');

            // Fermer tous les autres dropdowns
            document.querySelectorAll('[id^="dropdown-"]').forEach(function(d) {
                if (d.id !== 'dropdownActions') {
                    d.classList.add('hidden');
                }
            });

            if (isHidden) {
                const button = event.target.closest('button');
                if (button) {
                    const rect = button.getBoundingClientRect();
                    // Afficher d'abord pour mesurer la hauteur réelle
                    dropdown.style.visibility = 'hidden';
                    dropdown.classList.remove('hidden');
                    const dropdownHeight = dropdown.offsetHeight;
                    dropdown.classList.add('hidden');
                    dropdown.style.visibility = '';

                    const spaceBelow = window.innerHeight - rect.bottom - 8;
                    const spaceAbove = rect.top - 8;
                    if (spaceBelow < dropdownHeight && spaceAbove > spaceBelow) {
                        dropdown.style.top = '';
                        dropdown.style.bottom = (window.innerHeight - rect.top + 4) + 'px';
                        dropdown.style.maxHeight = spaceAbove + 'px';
                    } else {
                        dropdown.style.bottom = '';
                        dropdown.style.top = (rect.bottom + 4) + 'px';
                        dropdown.style.maxHeight = spaceBelow + 'px';
                    }
                    dropdown.style.right = (window.innerWidth - rect.right) + 'px';
                    dropdown.style.left = '';
                }
                dropdown.classList.remove('hidden');
            } else {
                dropdown.classList.add('hidden');
            }
        }

        function closeDropdownActions() {
            const dropdown = document.getElementById('dropdownActions');
            if (dropdown) {
                dropdown.classList.add('hidden');
            }
        }

        // Fermer les dropdowns en cliquant à l'extérieur
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.relative.inline-block') && !event.target.closest('[id^="dropdown-"]')) {
                document.querySelectorAll('[id^="dropdown-"]').forEach(function(dropdown) {
                    dropdown.classList.add('hidden');
                });
            }
        });

        // Fonction de confirmation pour la suppression complète
        function confirmSuppression(id) {
            const confirmed = confirm('⚠️ ATTENTION : Cette action est irréversible !\n\nÊtes-vous sûr de vouloir supprimer complètement cette inscription refusée ?\n\nCette action supprimera :\n- Toutes les données du pilote\n- Tous les documents associés\n- Tous les planeurs dont le pilote est propriétaire\n- Tous les messages associés\n\nCette action ne peut pas être annulée.');
            
            if (confirmed) {
                closeDropdown(id);
                return true;
            }
            
            return false;
        }
    </script>

    <!-- Modal Messagerie -->
    <div id="modalMessagerie" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-900" id="modalMessagerieTitre">Messagerie</h3>
                <button onclick="fermerModalMessagerie()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div id="modalMessagerieContent" class="space-y-4">
                <!-- Historique des messages -->
                <div class="border rounded-lg p-4 bg-gray-50" style="max-height: 400px; overflow-y: auto;">
                    <h4 class="font-semibold mb-3">Historique des messages</h4>
                    <div id="historiqueMessages" class="space-y-3">
                        <p class="text-gray-500 text-center">Chargement...</p>
                    </div>
                </div>
                
                <!-- Formulaire d'envoi -->
                <div class="border-t pt-4">
                    <h4 class="font-semibold mb-3">Envoyer un message</h4>
                    <form id="formMessage" onsubmit="envoyerMessage(event)" enctype="multipart/form-data">
                        <textarea id="messageText" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Tapez votre message ici..." required></textarea>
                        <div class="mt-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pièce jointe (optionnel, max 10MB)</label>
                            <input type="file" id="pieceJointe" name="piece_jointe" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" accept="*/*">
                        </div>
                        <div class="mt-3 flex justify-end gap-2">
                            <button type="button" onclick="fermerModalMessagerie()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                                Annuler
                            </button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                Envoyer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let piloteIdMessagerie = null;

        function ouvrirMessagerie(id) {
            piloteIdMessagerie = id;
            document.getElementById('modalMessagerie').classList.remove('hidden');
            chargerHistoriqueMessages(id);
        }

        function fermerModalMessagerie() {
            document.getElementById('modalMessagerie').classList.add('hidden');
            piloteIdMessagerie = null;
            document.getElementById('messageText').value = '';
            document.getElementById('historiqueMessages').innerHTML = '<p class="text-gray-500 text-center">Chargement...</p>';
        }

        function chargerHistoriqueMessages(id) {
            fetch(`/admin/pilotes/${id}/messages`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('modalMessagerieTitre').textContent = `Messagerie - ${data.pilote.prenom} ${data.pilote.nom}`;
                    
                    const historiqueDiv = document.getElementById('historiqueMessages');
                    if (data.messages.length === 0) {
                        historiqueDiv.innerHTML = '<p class="text-gray-500 text-center">Aucun message pour le moment.</p>';
                    } else {
                        historiqueDiv.innerHTML = data.messages.map(msg => `
                            <div class="bg-white p-3 rounded border-l-4 ${msg.user_name === 'Système' ? 'border-blue-500' : 'border-green-500'}">
                                <div class="flex justify-between items-start mb-2">
                                    <span class="font-semibold text-sm">${msg.user_name}</span>
                                    <span class="text-xs text-gray-500">${msg.created_at}</span>
                                </div>
                                <p class="text-gray-700 whitespace-pre-wrap">${msg.message}</p>
                                ${msg.piece_jointe ? `
                                    <div class="mt-2">
                                        <a href="/storage/${msg.piece_jointe}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                            </svg>
                                            Pièce jointe
                                        </a>
                                    </div>
                                ` : ''}
                            </div>
                        `).join('');
                        historiqueDiv.scrollTop = historiqueDiv.scrollHeight;
                    }
                })
                .catch(error => {
                    console.error('Erreur lors du chargement des messages:', error);
                    document.getElementById('historiqueMessages').innerHTML = '<p class="text-red-500 text-center">Erreur lors du chargement des messages.</p>';
                });
        }

        function envoyerMessage(event) {
            event.preventDefault();
            
            if (!piloteIdMessagerie) {
                alert('Erreur: ID pilote manquant');
                return;
            }

            const messageText = document.getElementById('messageText').value.trim();
            if (!messageText) {
                alert('Veuillez saisir un message');
                return;
            }

            const formData = new FormData();
            formData.append('message', messageText);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            
            const pieceJointe = document.getElementById('pieceJointe').files[0];
            if (pieceJointe) {
                formData.append('piece_jointe', pieceJointe);
            }

            fetch(`/admin/pilotes/${piloteIdMessagerie}/messages`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('messageText').value = '';
                    document.getElementById('pieceJointe').value = '';
                    chargerHistoriqueMessages(piloteIdMessagerie);
                } else {
                    alert('Erreur lors de l\'envoi du message');
                }
            })
            .catch(error => {
                console.error('Erreur lors de l\'envoi du message:', error);
                alert('Erreur lors de l\'envoi du message');
            });
        }

        // Fermer la modal en cliquant à l'extérieur
        document.getElementById('modalMessagerie').addEventListener('click', function(e) {
            if (e.target === this) {
                fermerModalMessagerie();
            }
        });

        // Fermer la modal avec Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !document.getElementById('modalMessagerie').classList.contains('hidden')) {
                fermerModalMessagerie();
            }
        });

        // Modal Message Groupe
        function ouvrirModalMessageGroupe() {
            document.getElementById('modalMessageGroupe').classList.remove('hidden');
        }

        function fermerModalMessageGroupe() {
            document.getElementById('modalMessageGroupe').classList.add('hidden');
            document.getElementById('formMessageGroupe').reset();
        }

        function envoyerMessageGroupe(event) {
            event.preventDefault();
            
            const formData = new FormData(document.getElementById('formMessageGroupe'));
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

            fetch('/admin/messages/envoyer-groupe', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    fermerModalMessageGroupe();
                    chargerTableMessagesGroupes();
                } else {
                    alert('Erreur lors de l\'envoi du message: ' + (data.error || 'Erreur inconnue'));
                }
            })
            .catch(error => {
                console.error('Erreur lors de l\'envoi du message:', error);
                alert('Erreur lors de l\'envoi du message');
            });
        }

        // Table Messages Groupés
        function ouvrirTableMessagesGroupes() {
            document.getElementById('modalTableMessagesGroupes').classList.remove('hidden');
            chargerTableMessagesGroupes();
        }

        function fermerTableMessagesGroupes() {
            document.getElementById('modalTableMessagesGroupes').classList.add('hidden');
        }

        function chargerTableMessagesGroupes() {
            fetch('/admin/messages/groupes')
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('tbodyMessagesGroupes');
                    if (data.messages.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-gray-500">Aucun message envoyé pour le moment.</td></tr>';
                    } else {
                        tbody.innerHTML = data.messages.map(msg => `
                            <tr class="border-b">
                                <td class="px-4 py-2">${msg.created_at}</td>
                                <td class="px-4 py-2">${msg.user_name}</td>
                                <td class="px-4 py-2">${msg.sujet || '-'}</td>
                                <td class="px-4 py-2">${msg.nombre_destinataires}</td>
                                <td class="px-4 py-2">
                                    <button onclick="voirDetailsMessageGroupe(${msg.id})" class="text-blue-600 hover:text-blue-800 text-sm">
                                        Voir détails
                                    </button>
                                    ${msg.piece_jointe ? `
                                        <a href="/storage/${msg.piece_jointe}" target="_blank" class="text-green-600 hover:text-green-800 text-sm ml-2">
                                            Télécharger PJ
                                        </a>
                                    ` : ''}
                                </td>
                            </tr>
                        `).join('');
                    }
                })
                .catch(error => {
                    console.error('Erreur lors du chargement des messages:', error);
                    document.getElementById('tbodyMessagesGroupes').innerHTML = '<tr><td colspan="5" class="text-center py-4 text-red-500">Erreur lors du chargement des messages.</td></tr>';
                });
        }

        function voirDetailsMessageGroupe(id) {
            fetch('/admin/messages/groupes')
                .then(response => response.json())
                .then(data => {
                    const msg = data.messages.find(m => m.id === id);
                    if (msg) {
                        alert(`Message:\n\n${msg.message}\n\nEnvoyé le: ${msg.created_at}\nDestinataires: ${msg.nombre_destinataires}`);
                    }
                });
        }
    </script>

    <!-- Modal Message Groupe -->
    <div id="modalMessageGroupe" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-900">Envoyer un message à tous les inscrits</h3>
                <button onclick="fermerModalMessageGroupe()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="formMessageGroupe" onsubmit="envoyerMessageGroupe(event)" enctype="multipart/form-data">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sujet (optionnel)</label>
                        <input type="text" name="sujet" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Sujet du message">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Message <span class="text-red-500">*</span></label>
                        <textarea name="message" rows="6" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Tapez votre message ici..." required></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pièce jointe (optionnel, max 10MB)</label>
                        <input type="file" name="piece_jointe" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" accept="*/*">
                    </div>
                    
                    <div class="flex justify-end gap-2 mt-4">
                        <button type="button" onclick="fermerModalMessageGroupe()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                            Annuler
                        </button>
                        <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700">
                            Envoyer à tous
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Table Messages Groupés -->
    <div id="modalTableMessagesGroupes" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-4/5 lg:w-3/4 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-900">Historique des messages envoyés</h3>
                <button onclick="fermerTableMessagesGroupes()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-300">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 border-b text-left">Date</th>
                            <th class="px-4 py-2 border-b text-left">Expéditeur</th>
                            <th class="px-4 py-2 border-b text-left">Sujet</th>
                            <th class="px-4 py-2 border-b text-left">Destinataires</th>
                            <th class="px-4 py-2 border-b text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyMessagesGroupes">
                        <tr>
                            <td colspan="5" class="text-center py-4 text-gray-500">Chargement...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Règlement -->
    <div id="modalReglement" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-900">Gérer le règlement de la compétition</h3>
                <button onclick="fermerModalReglement()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="formReglement" action="{{ route('admin.competition.reglement.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="space-y-4">
                    @if($competition && $competition->reglement)
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <p class="text-sm text-green-800 mb-2">
                            <strong>Règlement actuel :</strong> {{ basename($competition->reglement) }}
                        </p>
                        <a href="{{ route('reglement.public') }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm underline">
                            Voir le règlement actuel
                        </a>
                    </div>
                    @else
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <p class="text-sm text-yellow-800">
                            Aucun règlement n'est actuellement défini.
                        </p>
                    </div>
                    @endif
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ $competition && $competition->reglement ? 'Remplacer le règlement' : 'Téléverser le règlement' }} (PDF, max 10MB)
                        </label>
                        <input type="file" name="reglement" id="reglementFile" accept=".pdf" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Format accepté : PDF uniquement</p>
                    </div>
                    
                    <div class="flex justify-end gap-2 mt-4">
                        <button type="button" onclick="fermerModalReglement()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                            Annuler
                        </button>
                        @if($competition && $competition->reglement)
                        <button type="button" onclick="supprimerReglement()" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                            Supprimer
                        </button>
                        @endif
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            {{ $competition && $competition->reglement ? 'Remplacer' : 'Téléverser' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Code Aérodrome -->
    <div id="modalCodeAeroport" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-900">Configurer le code aérodrome de la compétition</h3>
                <button onclick="fermerModalCodeAeroport()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="formCodeAeroport" action="{{ route('admin.competition.code-aeroport.update') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    @if($competition && $competition->code_aeroport)
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <p class="text-sm text-green-800">
                            <strong>Code aérodrome actuel :</strong> <span class="font-mono font-bold">{{ $competition->code_aeroport }}</span>
                        </p>
                    </div>
                    @else
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <p class="text-sm text-yellow-800">
                            Aucun code aérodrome n'est actuellement défini.
                        </p>
                    </div>
                    @endif
                    
                    <div>
                        <label for="code_aeroport" class="block text-sm font-medium text-gray-700 mb-1">
                            Code aérodrome <span class="text-gray-500">(ex: LFTH, LFPO, etc.)</span>
                        </label>
                        <div class="flex gap-2">
                            <input 
                                type="text" 
                                name="code_aeroport" 
                                id="code_aeroport" 
                                value="{{ $competition->code_aeroport ?? '' }}"
                                maxlength="10"
                                class="flex-1 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono uppercase"
                                placeholder="LFTH"
                                oninput="this.value = this.value.toUpperCase()">
                            <button 
                                type="button" 
                                onclick="rechercherAeroport()" 
                                id="btnRechercherAeroport"
                                class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
                                <span id="btnRechercherText">Rechercher</span>
                                <span id="btnRechercherLoader" class="hidden">⏳</span>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Code ICAO de l'aérodrome (10 caractères max, sera converti en majuscules)</p>
                    </div>

                    <!-- Zone d'affichage des informations de l'aérodrome -->
                    <div id="airportInfo" class="hidden mt-4">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h4 class="font-semibold text-blue-900 mb-2">Informations de l'aérodrome</h4>
                            <div id="airportInfoContent" class="text-sm text-blue-800 space-y-1">
                                <!-- Les informations seront injectées ici -->
                            </div>
                        </div>
                    </div>

                    <!-- Zone d'affichage des données stockées -->
                    @if($competition && $competition->code_aeroport)
                    <div id="storedAirportData" class="mt-4">
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <div class="flex justify-between items-center mb-2">
                                <h4 class="font-semibold text-gray-900">Données stockées</h4>
                                <button 
                                    type="button" 
                                    onclick="chargerDonneesAeroport('{{ $competition->code_aeroport }}')" 
                                    class="text-xs px-2 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                                    Recharger
                                </button>
                            </div>
                            <div id="storedAirportDataContent" class="text-sm text-gray-700">
                                <p class="text-gray-500 italic">Chargement des données...</p>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <div class="flex justify-end gap-2 mt-4">
                        <button type="button" onclick="fermerModalCodeAeroport()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                            Annuler
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            Enregistrer
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Carte Points de Virage -->
    <div id="modalCartePointsVirage" class="hidden fixed inset-0 bg-gray-900 bg-opacity-90 overflow-hidden h-full w-full z-50">
        <div class="h-full w-full" style="display: grid; grid-template-columns: 70% 30%;">
            <!-- Carte (70% de la largeur) à gauche -->
            <div class="h-full relative">
                <div id="carte-points-virage" class="w-full h-full" style="min-height: 100vh;"></div>
            </div>
            <!-- Colonne de boutons (30% de la largeur) à droite -->
            <div class="h-full bg-white border-l border-gray-300 overflow-y-auto p-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Points de virage</h3>
                    <button onclick="fermerModalCartePointsVirage()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="space-y-2 mb-4">
                    <button onclick="activerModeEdition()" id="btnAjouterPoint" class="w-full px-3 py-2 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                        Ajouter un point de virage
                    </button>
                    <a href="{{ route('export.gps', 'cup') }}" class="w-full px-3 py-2 text-sm bg-green-600 text-white rounded hover:bg-green-700 transition inline-block text-center">
                        Télécharger .CUP
                    </a>
                </div>
                <div id="listePointsVirage" class="space-y-2 text-sm"></div>
            </div>
        </div>
    </div>

    <!-- Modal édition d'un point de virage -->
    <div id="modalEditPoint" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full" style="z-index: 60;">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-lg shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 id="modalEditPointTitre" class="text-lg font-bold text-gray-900">Point de virage</h3>
                <button onclick="fermerModalEditPoint()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="editPointFeedback" class="hidden mb-3 px-4 py-2 rounded text-sm"></div>
            <form id="formEditPoint" enctype="multipart/form-data">
                <input type="hidden" id="editPointId" value="">
                <input type="hidden" id="editPointLat" value="">
                <input type="hidden" id="editPointLng" value="">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                        <input type="text" id="editPointNom" required class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Coordonnées</label>
                        <p id="editPointCoords" class="text-sm text-gray-500 font-mono"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea id="editPointDescription" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Description du point de virage..."></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Image</label>
                        <div id="editPointImagePreview" class="mb-2 hidden">
                            <img id="editPointImageImg" src="" alt="" class="max-h-32 rounded border">
                        </div>
                        <input type="file" id="editPointImage" accept=".jpg,.jpeg,.png,.webp" class="w-full text-sm border border-gray-300 rounded px-2 py-1">
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" onclick="fermerModalEditPoint()" class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">
                        Annuler
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function ouvrirModalReglement() {
            document.getElementById('modalReglement').classList.remove('hidden');
        }

        function fermerModalReglement() {
            document.getElementById('modalReglement').classList.add('hidden');
            document.getElementById('formReglement').reset();
        }

        function ouvrirModalCodeAeroport() {
            document.getElementById('modalCodeAeroport').classList.remove('hidden');
            // Charger les données stockées si un code aéroport existe
            @if($competition && $competition->code_aeroport)
            chargerDonneesAeroport('{{ $competition->code_aeroport }}');
            @endif
        }

        function fermerModalCodeAeroport() {
            document.getElementById('modalCodeAeroport').classList.add('hidden');
            // Réinitialiser l'affichage
            document.getElementById('airportInfo').classList.add('hidden');
            document.getElementById('airportInfoContent').innerHTML = '';
        }

        let cartePointsVirage = null;
        let modeEdition = false;
        let clickHandler = null;
        function escHandler(e) { if (e.key === 'Escape') desactiverModeEdition(); }
        let pointsVirage = []; // [{id, nom, description, image, lat, lng, marker}]
        let baseCoords = null;
        let pendingLatLng = null; // Coordonnées en attente lors de l'ajout

        // Calcul de la distance en km (Haversine)
        function distanceKm(lat1, lng1, lat2, lng2) {
            const R = 6371;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLng = (lng2 - lng1) * Math.PI / 180;
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                      Math.sin(dLng / 2) * Math.sin(dLng / 2);
            return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        }

        // Créer un marqueur pour un point
        function creerMarqueur(point, numero) {
            const dist = baseCoords ? distanceKm(baseCoords[0], baseCoords[1], point.lat, point.lng).toFixed(1) : null;
            const icon = L.icon({
                iconUrl: `/img/marker/${numero}`,
                iconSize: [70, 60],
                iconAnchor: [35, 30],
                popupAnchor: [0, -30],
            });
            const marker = L.marker([point.lat, point.lng], { icon }).addTo(cartePointsVirage)
                .bindPopup(`<b>#${numero} ${point.nom}</b><br>${point.lat.toFixed(5)}, ${point.lng.toFixed(5)}${dist ? '<br>' + dist + ' km de la base' : ''}`);
            return marker;
        }

        // Ouvrir la modal d'édition pour un nouveau point (depuis le clic carte)
        function ouvrirModalNouveauPoint(lat, lng) {
            pendingLatLng = { lat, lng };
            document.getElementById('modalEditPointTitre').textContent = 'Nouveau point de virage';
            document.getElementById('editPointId').value = '';
            document.getElementById('editPointLat').value = lat;
            document.getElementById('editPointLng').value = lng;
            document.getElementById('editPointNom').value = 'Point ' + (pointsVirage.length + 1);
            document.getElementById('editPointCoords').textContent = lat.toFixed(5) + ', ' + lng.toFixed(5);
            document.getElementById('editPointDescription').value = '';
            document.getElementById('editPointImage').value = '';
            document.getElementById('editPointImagePreview').classList.add('hidden');
            document.getElementById('editPointFeedback').classList.add('hidden');
            document.getElementById('modalEditPoint').classList.remove('hidden');
            desactiverModeEdition();
        }

        // Ouvrir la modal d'édition pour un point existant
        function ouvrirModalEditerPoint(index) {
            const p = pointsVirage[index];
            if (!p) return;
            pendingLatLng = null;
            document.getElementById('modalEditPointTitre').textContent = 'Modifier le point de virage';
            document.getElementById('editPointId').value = p.id || '';
            document.getElementById('editPointLat').value = p.lat;
            document.getElementById('editPointLng').value = p.lng;
            document.getElementById('editPointNom').value = p.nom;
            document.getElementById('editPointCoords').textContent = p.lat.toFixed(5) + ', ' + p.lng.toFixed(5);
            document.getElementById('editPointDescription').value = p.description || '';
            document.getElementById('editPointImage').value = '';
            document.getElementById('editPointFeedback').classList.add('hidden');
            if (p.image) {
                document.getElementById('editPointImageImg').src = '/' + p.image;
                document.getElementById('editPointImagePreview').classList.remove('hidden');
            } else {
                document.getElementById('editPointImagePreview').classList.add('hidden');
            }
            document.getElementById('modalEditPoint').classList.remove('hidden');
        }

        function fermerModalEditPoint() {
            document.getElementById('modalEditPoint').classList.add('hidden');
            pendingLatLng = null;
        }

        // Soumission du formulaire d'édition (create ou update)
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('formEditPoint').addEventListener('submit', async function(e) {
                e.preventDefault();
                const feedback = document.getElementById('editPointFeedback');
                const id = document.getElementById('editPointId').value;
                const formData = new FormData();
                formData.append('nom', document.getElementById('editPointNom').value);
                formData.append('description', document.getElementById('editPointDescription').value);
                formData.append('latitude', document.getElementById('editPointLat').value);
                formData.append('longitude', document.getElementById('editPointLng').value);
                const imageFile = document.getElementById('editPointImage').files[0];
                if (imageFile) {
                    formData.append('image', imageFile);
                }

                try {
                    const url = id ? `/admin/points-virage/${id}` : '/admin/points-virage';
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                        body: formData,
                    });
                    const result = await response.json();
                    if (result.success) {
                        const pt = result.point;
                        if (id) {
                            // Mise à jour d'un point existant
                            const idx = pointsVirage.findIndex(p => String(p.id) === String(id));
                            if (idx !== -1) {
                                if (pointsVirage[idx].marker) cartePointsVirage.removeLayer(pointsVirage[idx].marker);
                                pointsVirage[idx] = pt;
                            }
                        } else {
                            // Nouveau point
                            pointsVirage.push(pt);
                        }
                        rafraichirListePoints();
                        fermerModalEditPoint();
                    } else {
                        feedback.classList.remove('hidden', 'bg-green-100', 'text-green-800');
                        feedback.classList.add('bg-red-100', 'text-red-800');
                        feedback.textContent = result.message || 'Erreur lors de l\'enregistrement.';
                    }
                } catch (err) {
                    feedback.classList.remove('hidden', 'bg-green-100', 'text-green-800');
                    feedback.classList.add('bg-red-100', 'text-red-800');
                    feedback.textContent = 'Erreur réseau.';
                }
            });
        });

        // Supprimer un point de virage
        async function supprimerPointVirage(index) {
            const point = pointsVirage[index];
            if (!point) return;
            if (!confirm('Supprimer le point "' + point.nom + '" ?')) return;

            if (point.id) {
                try {
                    await fetch(`/admin/points-virage/${point.id}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    });
                } catch (err) {
                    console.error('Erreur suppression:', err);
                }
            }
            if (point.marker) cartePointsVirage.removeLayer(point.marker);
            pointsVirage.splice(index, 1);
            rafraichirListePoints();
        }

        // Rafraîchir la liste dans le panneau droit et recréer les marqueurs
        function rafraichirListePoints() {
            // Recréer tous les marqueurs avec les bons numéros
            pointsVirage.forEach((p, i) => {
                if (p.marker) cartePointsVirage.removeLayer(p.marker);
                p.marker = creerMarqueur(p, i + 1);
            });

            const container = document.getElementById('listePointsVirage');
            if (!container) return;

            if (pointsVirage.length === 0) {
                container.innerHTML = '<p class="text-gray-400 italic">Aucun point de virage</p>';
                return;
            }

            container.innerHTML = pointsVirage.map((p, i) => {
                const num = i + 1;
                const dist = baseCoords ? distanceKm(baseCoords[0], baseCoords[1], p.lat, p.lng).toFixed(1) : null;
                return `
                    <div class="bg-gray-50 p-2 rounded border border-gray-200">
                        <div class="flex justify-between items-start">
                            <div class="cursor-pointer flex-1" onclick="cartePointsVirage.setView([${p.lat}, ${p.lng}], 13); pointsVirage[${i}].marker.openPopup();">
                                <div class="font-semibold text-gray-800"><span class="text-blue-600">#${num}</span> ${p.nom}</div>
                                <div class="text-xs text-gray-500">${p.lat.toFixed(5)}, ${p.lng.toFixed(5)}</div>
                                ${dist ? `<div class="text-xs text-blue-600">${dist} km de la base</div>` : ''}
                                ${p.description ? `<div class="text-xs text-gray-600 mt-1 truncate">${p.description}</div>` : ''}
                            </div>
                            <div class="flex gap-1 ml-2">
                                <button onclick="ouvrirModalEditerPoint(${i})" class="text-blue-400 hover:text-blue-600 p-1" title="Modifier">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                <button onclick="supprimerPointVirage(${i})" class="text-red-400 hover:text-red-600 p-1" title="Supprimer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        ${p.image ? `<img src="/${p.image}" alt="${p.nom}" class="mt-2 max-h-20 rounded border">` : ''}
                    </div>
                `;
            }).join('');
        }

        // Charger les points depuis le backend
        async function chargerPointsVirage() {
            try {
                const response = await fetch('/admin/points-virage', {
                    headers: { 'Accept': 'application/json' },
                });
                if (response.ok) {
                    const data = await response.json();
                    return data.success ? data.points : [];
                }
            } catch (err) {
                console.error('Erreur chargement points:', err);
            }
            return [];
        }

        function chargerLeaflet() {
            return new Promise((resolve) => {
                // Vérifier si Leaflet est déjà chargé
                if (typeof L !== 'undefined') {
                    resolve(L);
                    return;
                }

                // Charger le CSS de Leaflet
                if (!document.querySelector('link[href*="leaflet.css"]')) {
                    const leafletCSS = document.createElement('link');
                    leafletCSS.rel = 'stylesheet';
                    leafletCSS.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                    document.head.appendChild(leafletCSS);
                }

                // Charger le script Leaflet
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
                    resolve(L);
                };
                document.head.appendChild(leafletScript);
            });
        }

        async function regenererCarte() {
            const modal = document.getElementById('modalGenerationCarte');
            const statusText = document.getElementById('generation-carte-status');
            const subtitle = document.getElementById('generation-carte-subtitle');
            const spinner = document.getElementById('generation-carte-spinner');
            const iconSuccess = document.getElementById('generation-carte-success');
            const iconError = document.getElementById('generation-carte-error');
            const downloadLink = document.getElementById('generation-carte-download');
            const preview = document.getElementById('generation-carte-preview');

            // Afficher la modal en mode loading
            modal.classList.remove('hidden');
            statusText.textContent = 'Génération en cours...';
            subtitle.textContent = 'Cette opération peut prendre quelques minutes.';
            subtitle.classList.remove('hidden');
            spinner.classList.remove('hidden');
            iconSuccess.classList.add('hidden');
            iconError.classList.add('hidden');
            downloadLink.classList.add('hidden');
            preview.classList.add('hidden');

            try {
                const response = await fetch('{{ route("admin.carte.regenerer") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });
                const data = await response.json();
                spinner.classList.add('hidden');
                if (data.success) {
                    iconSuccess.classList.remove('hidden');
                    statusText.textContent = 'Carte générée avec succès !';
                    subtitle.classList.add('hidden');
                    downloadLink.classList.remove('hidden');
                    preview.src = '{{ route("export.carte") }}?t=' + Date.now();
                    preview.classList.remove('hidden');
                } else {
                    iconError.classList.remove('hidden');
                    statusText.textContent = 'Erreur : ' + (data.message || 'Erreur inconnue');
                    subtitle.classList.add('hidden');
                }
            } catch (e) {
                spinner.classList.add('hidden');
                iconError.classList.remove('hidden');
                statusText.textContent = 'Erreur lors de la génération de la carte.';
                subtitle.classList.add('hidden');
            }
        }

        async function ouvrirModalCartePointsVirage() {
            document.getElementById('modalCartePointsVirage').classList.remove('hidden');
            rafraichirListePoints();
            
            // Attendre que la modal soit visible avant d'initialiser la carte
            setTimeout(async () => {
                if (!cartePointsVirage) {
                    // Charger Leaflet depuis le CDN
                    const L = await chargerLeaflet();
                    
                    // Récupérer les coordonnées de l'aérodrome AVANT d'initialiser la carte
                    let center = [46.6, 2.5]; // Centre de la France par défaut
                    let zoom = 10;
                    let airportName = '';
                    let airportData = null;
                    
                    @if($competition && $competition->code_aeroport)
                        try {
                            const response = await fetch('{{ route("admin.competition.airport-data", ["icao" => $competition->code_aeroport]) }}');
                            if (response.ok) {
                                const data = await response.json();
                                console.log('Données aérodrome reçues:', data);
                                if (data.success && data.airport) {
                                    airportData = data.airport;
                                    
                                    // Vérifier différentes structures de données possibles
                                    let lat, lon;
                                    if (data.airport.geometry && data.airport.geometry.coordinates) {
                                        // Format GeoJSON: [longitude, latitude]
                                        lon = data.airport.geometry.coordinates[0];
                                        lat = data.airport.geometry.coordinates[1];
                                    } else if (data.airport.latitude && data.airport.longitude) {
                                        // Format direct
                                        lat = parseFloat(data.airport.latitude);
                                        lon = parseFloat(data.airport.longitude);
                                    }
                                    
                                    if (lat && lon) {
                                        center = [lat, lon];
                                        baseCoords = [lat, lon];
                                        zoom = 11;
                                        airportName = data.airport.name || '{{ $competition->code_aeroport }}';
                                        console.log('Coordonnées aérodrome:', center, 'Zoom:', zoom);
                                    } else {
                                        console.warn('Coordonnées non trouvées dans les données:', data.airport);
                                    }
                                }
                            } else {
                                console.error('Erreur HTTP:', response.status, response.statusText);
                            }
                        } catch (error) {
                            console.error('Erreur lors du chargement des données aérodrome:', error);
                        }
                    @endif
                    
                    // Initialiser la carte avec les coordonnées récupérées
                    cartePointsVirage = L.map('carte-points-virage').setView(center, zoom);
                    
                    // Ajouter la couche de tuiles OpenStreetMap
                    const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                        maxZoom: 19
                    }).addTo(cartePointsVirage);
                    
                    // Ajouter les tuiles OpenAIP par-dessus OSM
                    const openaipLayer = L.tileLayer('/api/openaip/tiles/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors | <a href="https://www.openaip.net">openAIP Data (CC-BY-NC)</a>',
                        minZoom: 4,
                        maxZoom: 14,
                        opacity: 0.7, // Transparence pour voir OSM en dessous
                        tileSize: 256,
                        detectRetina: true
                    }).addTo(cartePointsVirage);
                    
                    // Ajouter un marqueur sur l'aérodrome si les coordonnées sont disponibles
                    if (airportData) {
                        let lat, lon;
                        if (airportData.geometry && airportData.geometry.coordinates) {
                            lon = airportData.geometry.coordinates[0];
                            lat = airportData.geometry.coordinates[1];
                        } else if (airportData.latitude && airportData.longitude) {
                            lat = parseFloat(airportData.latitude);
                            lon = parseFloat(airportData.longitude);
                        }
                        
                        if (lat && lon) {
                            const airportMarker = L.marker([lat, lon])
                                .addTo(cartePointsVirage);
                            console.log('Marqueur ajouté à:', [lat, lon]);
                        }
                    }
                    
                    // Forcer le redimensionnement de la carte après un court délai
                    setTimeout(() => {
                        if (cartePointsVirage) {
                            cartePointsVirage.invalidateSize();
                        }
                    }, 200);

                    // Charger les points de virage existants depuis le backend
                    const pointsExistants = await chargerPointsVirage();
                    pointsExistants.forEach(p => pointsVirage.push(p));
                    rafraichirListePoints();
                } else {
                    // Si la carte existe déjà, recentrer sur l'aérodrome et forcer le redimensionnement
                    cartePointsVirage.invalidateSize();
                    
                    @if($competition && $competition->code_aeroport)
                        try {
                            const response = await fetch('{{ route("admin.competition.airport-data", ["icao" => $competition->code_aeroport]) }}');
                            if (response.ok) {
                                const data = await response.json();
                                if (data.success && data.airport) {
                                    let lat, lon;
                                    if (data.airport.geometry && data.airport.geometry.coordinates) {
                                        lon = data.airport.geometry.coordinates[0];
                                        lat = data.airport.geometry.coordinates[1];
                                    } else if (data.airport.latitude && data.airport.longitude) {
                                        lat = parseFloat(data.airport.latitude);
                                        lon = parseFloat(data.airport.longitude);
                                    }
                                    
                                    if (lat && lon) {
                                        cartePointsVirage.setView([lat, lon], 11);
                                    }
                                }
                            }
                        } catch (error) {
                            console.error('Erreur lors du chargement des données aérodrome:', error);
                        }
                    @endif
                }
            }, 200);
        }

        function fermerModalCartePointsVirage() {
            document.getElementById('modalCartePointsVirage').classList.add('hidden');
            // Désactiver le mode édition si actif
            desactiverModeEdition();
        }

        function activerModeEdition() {
            if (!cartePointsVirage) {
                console.warn('La carte n\'est pas encore initialisée');
                return;
            }

            modeEdition = true;

            // Griser le bouton
            const btn = document.getElementById('btnAjouterPoint');
            btn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
            btn.classList.add('bg-gray-400', 'cursor-not-allowed');
            btn.disabled = true;

            // Écouter la touche Échap
            document.addEventListener('keydown', escHandler);

            // Désactiver le déplacement et le zoom de la carte
            cartePointsVirage.dragging.disable();
            cartePointsVirage.scrollWheelZoom.disable();
            cartePointsVirage.doubleClickZoom.disable();
            cartePointsVirage.touchZoom.disable();
            cartePointsVirage.boxZoom.disable();

            // Appliquer le curseur crosshair sur le conteneur parent de la carte
            const carteElement = document.getElementById('carte-points-virage');
            if (carteElement) {
                carteElement.classList.add('carte-mode-edition');
            }

            // Ajouter un listener sur la carte pour ouvrir la modal de création
            clickHandler = function(e) {
                ouvrirModalNouveauPoint(e.latlng.lat, e.latlng.lng);
            };

            cartePointsVirage.on('click', clickHandler);

            // Écouter les clics hors carte pour désactiver le mode édition
            // setTimeout pour ne pas capter le clic du bouton "Ajouter" lui-même
            setTimeout(() => {
                document.addEventListener('click', desactiverModeEditionSiHorsCarte);
            }, 0);
        }

        function desactiverModeEdition() {
            if (!modeEdition) return;
            modeEdition = false;

            // Restaurer le bouton bleu
            const btn = document.getElementById('btnAjouterPoint');
            btn.classList.remove('bg-gray-400', 'cursor-not-allowed');
            btn.classList.add('bg-blue-600', 'hover:bg-blue-700');
            btn.disabled = false;

            // Retirer le listener Échap
            document.removeEventListener('keydown', escHandler);

            if (cartePointsVirage) {
                // Réactiver le déplacement et le zoom
                cartePointsVirage.dragging.enable();
                cartePointsVirage.scrollWheelZoom.enable();
                cartePointsVirage.doubleClickZoom.enable();
                cartePointsVirage.touchZoom.enable();
                cartePointsVirage.boxZoom.enable();
            }

            // Retirer le curseur crosshair
            const carteElement = document.getElementById('carte-points-virage');
            if (carteElement) {
                carteElement.classList.remove('carte-mode-edition');
            }

            // Retirer le listener de clic sur la carte
            if (cartePointsVirage && clickHandler) {
                cartePointsVirage.off('click', clickHandler);
                clickHandler = null;
            }

            // Retirer le listener sur le document
            document.removeEventListener('click', desactiverModeEditionSiHorsCarte);
        }

        function desactiverModeEditionSiHorsCarte(event) {
            const carteElement = document.getElementById('carte-points-virage');
            // Ignorer si clic sur le bouton "Ajouter un point de virage"
            if (event.target.closest('button[onclick*="activerModeEdition"]')) return;
            if (carteElement && !carteElement.contains(event.target)) {
                desactiverModeEdition();
            }
        }

        function rechercherAeroport() {
            const codeInput = document.getElementById('code_aeroport');
            const icaoCode = codeInput.value.trim().toUpperCase();
            
            if (!icaoCode) {
                alert('Veuillez saisir un code ICAO');
                return;
            }

            const btnRechercher = document.getElementById('btnRechercherAeroport');
            const btnText = document.getElementById('btnRechercherText');
            const btnLoader = document.getElementById('btnRechercherLoader');
            
            // Afficher le loader
            btnRechercher.disabled = true;
            btnText.classList.add('hidden');
            btnLoader.classList.remove('hidden');
            
            // Cacher les anciennes informations
            document.getElementById('airportInfo').classList.add('hidden');
            
            // Appeler l'API
            fetch('{{ route("admin.competition.search-airport") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ icao: icaoCode })
            })
            .then(response => response.json())
            .then(data => {
                // Réinitialiser le bouton
                btnRechercher.disabled = false;
                btnText.classList.remove('hidden');
                btnLoader.classList.add('hidden');
                
                if (data.success && data.airport) {
                    afficherInfosAeroport(data.airport);
                } else {
                    alert('Aérodrome non trouvé pour le code: ' + icaoCode);
                }
            })
            .catch(error => {
                console.error('Erreur lors de la recherche:', error);
                alert('Erreur lors de la recherche de l\'aérodrome');
                
                // Réinitialiser le bouton
                btnRechercher.disabled = false;
                btnText.classList.remove('hidden');
                btnLoader.classList.add('hidden');
            });
        }

        function afficherInfosAeroport(airport) {
            const infoDiv = document.getElementById('airportInfo');
            const contentDiv = document.getElementById('airportInfoContent');
            
            let html = '';
            
            if (airport.name) {
                html += `<p><strong>Nom:</strong> ${airport.name}</p>`;
            }
            if (airport.icaoCode) {
                html += `<p><strong>Code ICAO:</strong> <span class="font-mono">${airport.icaoCode}</span></p>`;
            }
            if (airport.iataCode) {
                html += `<p><strong>Code IATA:</strong> <span class="font-mono">${airport.iataCode}</span></p>`;
            }
            
            // Géométrie (coordonnées)
            if (airport.geometry && airport.geometry.coordinates) {
                const coords = airport.geometry.coordinates;
                html += `<p><strong>Coordonnées:</strong> ${coords[1]}, ${coords[0]} (lat, lon)</p>`;
            }
            
            if (airport.country) {
                html += `<p><strong>Pays:</strong> ${airport.country}</p>`;
            }
            
            if (airport.runways && Array.isArray(airport.runways) && airport.runways.length > 0) {
                html += `<p><strong>Pistes:</strong> ${airport.runways.length} piste(s)</p>`;
                airport.runways.forEach((runway, index) => {
                    if (runway.designator) {
                        html += `<p class="ml-4 text-xs">- Piste ${runway.designator}`;
                        if (runway.dimension && runway.dimension.length) {
                            html += ` (${runway.dimension.length.value} ${runway.dimension.length.unit === 0 ? 'm' : 'ft'})`;
                        }
                        html += `</p>`;
                    }
                });
            }
            
            if (airport.elevation) {
                const unit = airport.elevation.unit === 0 ? 'm' : (airport.elevation.unit === 1 ? 'ft' : '');
                html += `<p><strong>Altitude:</strong> ${airport.elevation.value} ${unit}</p>`;
            }
            
            // Fréquences
            if (airport.frequencies && Array.isArray(airport.frequencies) && airport.frequencies.length > 0) {
                html += `<p><strong>Fréquences:</strong></p>`;
                airport.frequencies.forEach((freq) => {
                    if (freq.value && freq.name) {
                        html += `<p class="ml-4 text-xs">- ${freq.name}: ${freq.value} ${freq.unit === 2 ? 'MHz' : ''}</p>`;
                    }
                });
            }
            
            if (!html) {
                html = '<p class="text-gray-500">Informations limitées disponibles</p>';
            }
            
            contentDiv.innerHTML = html;
            infoDiv.classList.remove('hidden');
        }

        function chargerDonneesAeroport(icaoCode) {
            const contentDiv = document.getElementById('storedAirportDataContent');
            if (!contentDiv) return;
            
            contentDiv.innerHTML = '<p class="text-gray-500 italic">Chargement des données...</p>';
            
            fetch(`{{ url('/admin/competition/airport-data') }}/${icaoCode}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.airport) {
                        afficherDonneesStockees(data.airport);
                    } else {
                        contentDiv.innerHTML = '<p class="text-gray-500 italic">Aucune donnée stockée pour cet aérodrome</p>';
                    }
                })
                .catch(error => {
                    console.error('Erreur lors du chargement des données:', error);
                    contentDiv.innerHTML = '<p class="text-red-500">Erreur lors du chargement des données</p>';
                });
        }

        // Fonction pour obtenir le type de surface d'une piste
        function getSurfaceTypeAdmin(surface) {
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

        function afficherDonneesStockees(airport) {
            const contentDiv = document.getElementById('storedAirportDataContent');
            if (!contentDiv) return;
            
            let html = '';
            
            if (airport.name) {
                html += `<p><strong>Nom:</strong> ${airport.name}</p>`;
            }
            if (airport.icaoCode) {
                html += `<p><strong>Code ICAO:</strong> <span class="font-mono">${airport.icaoCode}</span></p>`;
            }
            if (airport.iataCode) {
                html += `<p><strong>Code IATA:</strong> <span class="font-mono">${airport.iataCode}</span></p>`;
            }
            
            // Géométrie (coordonnées)
            if (airport.geometry && airport.geometry.coordinates) {
                const coords = airport.geometry.coordinates;
                html += `<p><strong>Coordonnées:</strong> ${coords[1]}, ${coords[0]} (lat, lon)</p>`;
            }
            
            if (airport.country) {
                html += `<p><strong>Pays:</strong> ${airport.country}</p>`;
            }
            
            if (airport.runways && Array.isArray(airport.runways) && airport.runways.length > 0) {
                html += `<p><strong>Pistes:</strong> ${airport.runways.length} piste(s)</p>`;
                airport.runways.forEach((runway, index) => {
                    if (runway.designator) {
                        html += `<p class="ml-4 text-xs">- Piste ${runway.designator}`;
                        if (runway.dimension && runway.dimension.length) {
                            html += ` (${runway.dimension.length.value} ${runway.dimension.length.unit === 0 ? 'm' : 'ft'})`;
                        }
                        if (runway.surface) {
                            const surfaceType = getSurfaceTypeAdmin(runway.surface);
                            html += ` - Surface: ${surfaceType}`;
                        }
                        html += `</p>`;
                    }
                });
            }
            
            if (airport.elevation) {
                const unit = airport.elevation.unit === 0 ? 'm' : (airport.elevation.unit === 1 ? 'ft' : '');
                html += `<p><strong>Altitude:</strong> ${airport.elevation.value} ${unit}</p>`;
            }
            
            // Fréquences
            if (airport.frequencies && Array.isArray(airport.frequencies) && airport.frequencies.length > 0) {
                html += `<p><strong>Fréquences:</strong></p>`;
                airport.frequencies.forEach((freq) => {
                    if (freq.value && freq.name) {
                        html += `<p class="ml-4 text-xs">- ${freq.name}: ${freq.value} ${freq.unit === 2 ? 'MHz' : ''}</p>`;
                    }
                });
            }
            
            // Afficher un lien pour voir le JSON complet
            html += `<p class="mt-2 text-xs"><a href="#" onclick="afficherJsonComplet(event)" class="text-blue-600 hover:text-blue-800">Voir le JSON complet</a></p>`;
            html += `<pre id="jsonComplet" class="hidden mt-2 text-xs bg-gray-100 p-2 rounded overflow-auto max-h-64"></pre>`;
            
            contentDiv.innerHTML = html;
            
            // Stocker les données pour l'affichage JSON
            window.airportDataJson = JSON.stringify(airport, null, 2);
        }

        function afficherJsonComplet(event) {
            event.preventDefault();
            const jsonPre = document.getElementById('jsonComplet');
            if (jsonPre && window.airportDataJson) {
                jsonPre.textContent = window.airportDataJson;
                jsonPre.classList.toggle('hidden');
            }
        }

        // Fonctions pour la modal de réponse aux messages de contact
        function ouvrirModalReponseContact(id, nom, email, message) {
            const modal = document.getElementById('modalReponseContact');
            const form = document.getElementById('formReponseContact');
            
            // Mettre à jour le formulaire avec l'ID du message
            form.action = '{{ url("/admin/contact") }}/' + id + '/repondre';
            
            // Afficher les informations du contact
            document.getElementById('contact_nom_display').textContent = nom;
            document.getElementById('contact_email_display').textContent = '(' + email + ')';
            document.getElementById('contact_message_display').textContent = message;
            
            // Réinitialiser le champ de réponse
            document.getElementById('contact_reponse').value = '';
            
            // Afficher la modal
            modal.classList.remove('hidden');
        }

        function fermerModalReponseContact() {
            document.getElementById('modalReponseContact').classList.add('hidden');
            document.getElementById('formReponseContact').reset();
        }

        // Fermer la modal en cliquant en dehors
        document.getElementById('modalReponseContact').addEventListener('click', function(e) {
            if (e.target === this) {
                fermerModalReponseContact();
            }
        });

        // Fermer la modal avec Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !document.getElementById('modalReponseContact').classList.contains('hidden')) {
                fermerModalReponseContact();
            }
        });

        function supprimerReglement() {
            if (confirm('Êtes-vous sûr de vouloir supprimer le règlement ?')) {
                const form = document.getElementById('formReglement');
                const formData = new FormData();
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                
                fetch('{{ route("admin.competition.reglement.update") }}', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (response.ok) {
                        window.location.reload();
                    } else {
                        alert('Erreur lors de la suppression du règlement');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur lors de la suppression du règlement');
                });
            }
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
            if (e.key === 'Escape' && !document.getElementById('modalCodeAeroport').classList.contains('hidden')) {
                fermerModalCodeAeroport();
            }
        });

        // Fermer la modal du code aéroport en cliquant à l'extérieur
        document.getElementById('modalCodeAeroport').addEventListener('click', function(e) {
            if (e.target === this) {
                fermerModalCodeAeroport();
            }
        });

        // Polling automatique des paiements HelloAsso en attente au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            const paiementsEnAttente = @json($paiementsHelloAssoEnAttente);
            
            // Filtrer uniquement les paiements non validés (double vérification côté client)
            const paiementsNonValides = paiementsEnAttente.filter(p => !p.paiement_valide);
            
            if (paiementsNonValides && paiementsNonValides.length > 0) {
                console.log(`[HelloAsso Polling] Début du polling pour ${paiementsNonValides.length} paiement(s) en attente (${paiementsEnAttente.length - paiementsNonValides.length} déjà validé(s) ignoré(s))`);
                
                // Fonction pour mettre à jour le statut dans la table
                function updateStatut(piloteId, statut, details) {
                    const statutElement = document.getElementById(`statut-${piloteId}`);
                    if (!statutElement) return;

                    // Supprimer les classes existantes
                    statutElement.className = 'px-2 py-1 rounded text-sm font-medium';
                    
                    let badgeClass = '';
                    let badgeText = '';
                    let icon = '';

                    if (statut === 'valide') {
                        badgeClass = 'bg-green-100 text-green-800';
                        badgeText = '✅ Paiement validé';
                        icon = '✓';
                        if (details.orderId) {
                            badgeText += ` (Order: ${details.orderId})`;
                        }
                    } else if (statut === 'en_attente') {
                        badgeClass = 'bg-yellow-100 text-yellow-800';
                        badgeText = 'En attente';
                        icon = '⏳';
                    } else if (statut === 'erreur') {
                        badgeClass = 'bg-red-100 text-red-800';
                        badgeText = '❌ Erreur';
                        icon = '✗';
                        if (details.error) {
                            badgeText += `: ${details.error}`;
                        }
                    } else if (statut === 'verification') {
                        badgeClass = 'bg-gray-100 text-gray-600';
                        badgeText = 'Vérification...';
                        icon = '<span class="inline-block w-2 h-2 rounded-full bg-gray-400 mr-1 animate-pulse"></span>';
                    }

                    statutElement.className = `px-2 py-1 rounded text-sm font-medium ${badgeClass}`;
                    statutElement.innerHTML = icon ? `${icon} ${badgeText}` : badgeText;
                }

                // Fonction pour appeler l'API de polling pour un paiement
                async function checkPaiementStatus(paiement, index) {
                    const checkoutIntentId = paiement.helloasso_checkout_intent_id;
                    
                    if (!checkoutIntentId || checkoutIntentId.trim() === '') {
                        console.warn(`[HelloAsso Polling] Paiement ${index + 1}/${paiementsEnAttente.length} (${paiement.nom} ${paiement.prenom}): CheckoutIntentId manquant`);
                        updateStatut(paiement.id, 'erreur', { error: 'CheckoutIntentId manquant' });
                        return;
                    }

                    try {
                        console.log(`[HelloAsso Polling] Vérification du paiement ${index + 1}/${paiementsEnAttente.length} (${paiement.nom} ${paiement.prenom}) - CheckoutIntentId: ${checkoutIntentId}`);
                        
                        const url = `{{ url('/api/paiement/check') }}/${checkoutIntentId}`;
                        const response = await fetch(url, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            }
                        });

                        const data = await response.json();
                        
                        console.log(`[HelloAsso Polling] Résultat pour ${paiement.nom} ${paiement.prenom} (CheckoutIntentId: ${checkoutIntentId}):`, JSON.stringify(data, null, 2));
                        
                        if (data.success && data.checkoutData) {
                            console.log(`[HelloAsso Polling] Données complètes du checkout:`, JSON.stringify(data.checkoutData, null, 2));
                            
                            if (data.paiementValide) {
                                console.log(`[HelloAsso Polling] ✅ Paiement validé pour ${paiement.nom} ${paiement.prenom} - OrderId: ${data.orderId || 'N/A'}`);
                                
                                // Extraire les détails du statut depuis checkoutData
                                const order = data.checkoutData.order;
                                let statutDetails = {
                                    orderId: data.orderId,
                                    itemState: order?.items?.[0]?.state || 'N/A',
                                    paymentState: order?.payments?.[0]?.state || 'N/A',
                                    cashOutState: order?.payments?.[0]?.cashOutState || 'N/A',
                                    date: order?.date || 'N/A'
                                };
                                
                                updateStatut(paiement.id, 'valide', statutDetails);
                            } else {
                                console.log(`[HelloAsso Polling] ⏳ Paiement toujours en attente pour ${paiement.nom} ${paiement.prenom}`);
                                updateStatut(paiement.id, 'en_attente', {});
                            }
                        } else {
                            console.error(`[HelloAsso Polling] ❌ Erreur pour ${paiement.nom} ${paiement.prenom}:`, data.error || 'Erreur inconnue');
                            updateStatut(paiement.id, 'erreur', { error: data.error || 'Erreur inconnue' });
                        }
                    } catch (error) {
                        console.error(`[HelloAsso Polling] ❌ Exception pour ${paiement.nom} ${paiement.prenom}:`, error);
                        updateStatut(paiement.id, 'erreur', { error: error.message || 'Exception' });
                    }
                }

                // Appeler les paiements les uns après les autres (séquentiellement)
                async function pollAllPaiements() {
                    for (let i = 0; i < paiementsNonValides.length; i++) {
                        // Vérifier une dernière fois que le paiement n'est pas déjà validé
                        if (paiementsNonValides[i].paiement_valide) {
                            console.log(`[HelloAsso Polling] Paiement ${i + 1}/${paiementsNonValides.length} (${paiementsNonValides[i].nom} ${paiementsNonValides[i].prenom}) déjà validé, ignoré`);
                            continue;
                        }
                        await checkPaiementStatus(paiementsNonValides[i], i);
                        // Attendre 500ms entre chaque appel pour ne pas surcharger l'API
                        if (i < paiementsNonValides.length - 1) {
                            await new Promise(resolve => setTimeout(resolve, 500));
                        }
                    }
                    console.log(`[HelloAsso Polling] Polling terminé pour tous les paiements`);
                }

                // Démarrer le polling
                pollAllPaiements();
            } else {
                console.log('[HelloAsso Polling] Aucun paiement HelloAsso en attente (non validé)');
            }
        });
    </script>

    <script>
        // Todo list
        const statutColors = {
            'A faire': 'bg-red-100 text-red-800',
            'En cours': 'bg-yellow-100 text-yellow-800',
            'Fait': 'bg-green-100 text-green-800',
        };
        const statutCycle = ['A faire', 'En cours', 'Fait'];

        function renderTaches(taches) {
            const tbody = document.getElementById('tachesBody');
            if (taches.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="px-4 py-4 text-center text-gray-500">Aucune tâche</td></tr>';
                return;
            }
            tbody.innerHTML = taches.map(t => `
                <tr>
                    <td class="px-4 py-2 border-b">
                        <div class="flex items-center gap-2">
                            <button onclick="toggleCommentaires(${t.id})" class="relative text-gray-400 hover:text-blue-600 shrink-0" title="Commentaires">
                                ${t.commentaires_count > 0
                                    ? `<span class="bg-blue-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">${t.commentaires_count}</span>`
                                    : `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                    </svg>`}
                            </button>
                            <span class="editable-cell cursor-pointer hover:bg-blue-50 px-1 rounded" onclick="editCell(this, ${t.id}, 'intitule')">${escapeHtml(t.intitule)}</span>
                        </div>
                    </td>
                    <td class="px-4 py-2 border-b">
                        <span class="editable-cell cursor-pointer hover:bg-blue-50 px-1 rounded" onclick="editCell(this, ${t.id}, 'personne')">${escapeHtml(t.personne || '')}&nbsp;</span>
                    </td>
                    <td class="px-4 py-2 border-b w-32">
                        <button onclick="cyclerStatut(${t.id}, '${t.statut}')" class="px-2 py-1 rounded-full text-xs font-semibold cursor-pointer ${statutColors[t.statut] || 'bg-gray-100 text-gray-800'}">
                            ${escapeHtml(t.statut)}
                        </button>
                    </td>
                    <td class="px-4 py-2 border-b">
                        <button onclick="supprimerTache(${t.id})" class="text-red-400 hover:text-red-600" title="Supprimer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </td>
                </tr>
                <tr id="commentaires-row-${t.id}" class="hidden">
                    <td colspan="4" class="px-4 py-3 bg-gray-50 border-b">
                        <div id="commentaires-container-${t.id}">
                            <p class="text-gray-400 text-sm">Chargement...</p>
                        </div>
                        <div class="flex gap-2 mt-2">
                            <input type="text" id="commentaire-input-${t.id}" placeholder="Ajouter un commentaire..." class="flex-1 px-2 py-1 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onkeydown="if(event.key==='Enter')ajouterCommentaire(${t.id})">
                            <button onclick="ajouterCommentaire(${t.id})" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">Ajouter</button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function chargerTaches() {
            fetch('{{ route("admin.taches.index") }}')
                .then(r => r.json())
                .then(taches => renderTaches(taches));
        }

        function ajouterTache() {
            const intitule = document.getElementById('newTacheIntitule').value.trim();
            const personne = document.getElementById('newTachePersonne').value.trim();
            if (!intitule) return;

            fetch('{{ route("admin.taches.save") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ intitule, personne }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('newTacheIntitule').value = '';
                    document.getElementById('newTachePersonne').value = '';
                    chargerTaches();
                }
            });
        }

        function cyclerStatut(id, statut) {
            const idx = statutCycle.indexOf(statut);
            const next = statutCycle[(idx + 1) % statutCycle.length];

            fetch(`/admin/taches/${id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ statut: next }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) chargerTaches();
            });
        }

        function supprimerTache(id) {
            fetch(`/admin/taches/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) chargerTaches();
            });
        }

        function editCell(span, id, field) {
            const currentValue = span.textContent.trim();
            const input = document.createElement('input');
            input.type = 'text';
            input.value = currentValue === '\u00a0' ? '' : currentValue;
            input.className = 'w-full px-2 py-1 border border-blue-400 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none text-sm';

            input.addEventListener('blur', () => saveCell(input, id, field));
            input.addEventListener('keydown', e => {
                if (e.key === 'Enter') input.blur();
                if (e.key === 'Escape') { chargerTaches(); }
            });

            span.replaceWith(input);
            input.focus();
            input.select();
        }

        function saveCell(input, id, field) {
            const value = input.value.trim();
            fetch(`/admin/taches/${id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ [field]: value }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) chargerTaches();
            });
        }

        // Permettre l'ajout avec Entrée
        document.getElementById('newTacheIntitule').addEventListener('keydown', e => {
            if (e.key === 'Enter') ajouterTache();
        });
        document.getElementById('newTachePersonne').addEventListener('keydown', e => {
            if (e.key === 'Enter') ajouterTache();
        });

        const commentairesCharges = {};

        function toggleCommentaires(id) {
            const row = document.getElementById(`commentaires-row-${id}`);
            if (row.classList.contains('hidden')) {
                row.classList.remove('hidden');
                if (!commentairesCharges[id]) {
                    chargerCommentaires(id);
                }
            } else {
                row.classList.add('hidden');
            }
        }

        function chargerCommentaires(id) {
            fetch(`/admin/taches/${id}/commentaires`)
                .then(r => r.json())
                .then(commentaires => {
                    commentairesCharges[id] = true;
                    const container = document.getElementById(`commentaires-container-${id}`);
                    if (commentaires.length === 0) {
                        container.innerHTML = '<p class="text-gray-400 text-sm italic">Aucun commentaire</p>';
                        return;
                    }
                    container.innerHTML = commentaires.map(c => `
                        <div class="flex justify-between items-start py-1 border-b border-gray-200 last:border-0">
                            <div>
                                <span class="text-xs text-gray-400">${new Date(c.created_at).toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })}</span>
                                <span class="text-sm ml-2">${escapeHtml(c.contenu)}</span>
                            </div>
                            <button onclick="supprimerCommentaire(${c.id}, ${id})" class="text-red-400 hover:text-red-600 text-xs ml-2 shrink-0">Suppr.</button>
                        </div>
                    `).join('');
                });
        }

        function ajouterCommentaire(id) {
            const input = document.getElementById(`commentaire-input-${id}`);
            const contenu = input.value.trim();
            if (!contenu) return;

            fetch(`/admin/taches/${id}/commentaires`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ contenu }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    input.value = '';
                    chargerCommentaires(id);
                    chargerTaches();
                }
            });
        }

        function supprimerCommentaire(commentaireId, tacheId) {
            fetch(`/admin/commentaires-taches/${commentaireId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    chargerCommentaires(tacheId);
                    chargerTaches();
                }
            });
        }

        // Charger les tâches au chargement
        chargerTaches();
    </script>

    <!-- Modal Gestion des Administrateurs -->
    <div id="modalAdmins" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-2xl font-bold text-gray-900">Gestion des administrateurs</h3>
                <button onclick="fermerModalAdmins()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div id="adminsMessage" style="display:none;" class="mb-3 px-4 py-2 rounded text-sm"></div>

            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-300">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 border-b text-left text-sm font-medium text-gray-700">Nom</th>
                            <th class="px-4 py-2 border-b text-left text-sm font-medium text-gray-700">Email</th>
                            <th class="px-4 py-2 border-b text-left text-sm font-medium text-gray-700">Dernière connexion</th>
                            <th class="px-4 py-2 border-b text-left text-sm font-medium text-gray-700" style="width:160px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="adminsTableBody">
                        <tr><td colspan="4" class="px-4 py-3 text-center text-gray-500">Chargement...</td></tr>
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50">
                            <td class="px-4 py-2 border-t"><input type="text" id="newAdminName" placeholder="Nom" class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"></td>
                            <td class="px-4 py-2 border-t"><input type="email" id="newAdminEmail" placeholder="Email" class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"></td>
                            <td class="px-4 py-2 border-t"></td>
                            <td class="px-4 py-2 border-t"><button onclick="ajouterAdmin()" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm">Ajouter</button></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <script>
    (function() {
        var currentUserId = {{ Auth::id() }};
        var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function showMsg(text, isError) {
            var el = document.getElementById('adminsMessage');
            el.className = 'mb-3 px-4 py-2 rounded text-sm ' + (isError ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800');
            el.textContent = text;
            el.style.display = 'block';
            setTimeout(function() { el.style.display = 'none'; }, 4000);
        }

        function escHtml(s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
        function escAttr(s) { return s.replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;'); }

        function chargerAdmins() {
            var tbody = document.getElementById('adminsTableBody');
            tbody.innerHTML = '<tr><td colspan="3" class="px-4 py-3 text-center text-gray-500">Chargement...</td></tr>';
            fetch('{{ route("admin.admins.index") }}', { headers: { 'Accept': 'application/json' } })
                .then(function(r) { return r.json(); })
                .then(function(admins) {
                    tbody.innerHTML = '';
                    if (admins.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="4" class="px-4 py-3 text-center text-gray-500">Aucun administrateur.</td></tr>';
                        return;
                    }
                    admins.forEach(function(admin) {
                        var isSelf = admin.id === currentUserId;
                        var loginInfo = admin.last_login_at
                            ? '<span class="text-green-700">' + new Date(admin.last_login_at).toLocaleDateString('fr-FR', {day:'2-digit',month:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit'}) + '</span>'
                            : '<span class="text-red-500 italic">Jamais connecté</span>';
                        var tr = document.createElement('tr');
                        tr.className = 'hover:bg-gray-50';
                        tr.innerHTML =
                            '<td class="px-4 py-2 border-b text-sm">' + escHtml(admin.name) + '</td>' +
                            '<td class="px-4 py-2 border-b text-sm">' + escHtml(admin.email) + '</td>' +
                            '<td class="px-4 py-2 border-b text-sm">' + loginInfo + '</td>' +
                            '<td class="px-4 py-2 border-b text-sm">' +
                                '<button onclick="modifierAdminRow(' + admin.id + ', this)" class="text-blue-600 hover:text-blue-800 mr-2 text-sm" title="Modifier">Modifier</button>' +
                                (isSelf ? '<span class="text-gray-400 text-xs">(vous)</span>' : '<button onclick="supprimerAdmin(' + admin.id + ')" class="text-red-600 hover:text-red-800 text-sm" title="Supprimer">Supprimer</button>') +
                            '</td>';
                        tbody.appendChild(tr);
                    });
                })
                .catch(function() { showMsg('Erreur lors du chargement.', true); });
        }

        window.ouvrirModalAdmins = function() {
            document.getElementById('modalAdmins').classList.remove('hidden');
            chargerAdmins();
        };

        window.fermerModalAdmins = function() {
            document.getElementById('modalAdmins').classList.add('hidden');
        };

        // Fermeture clic extérieur
        document.getElementById('modalAdmins').addEventListener('click', function(e) {
            if (e.target === this) fermerModalAdmins();
        });

        // Fermeture Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !document.getElementById('modalAdmins').classList.contains('hidden')) {
                fermerModalAdmins();
            }
        });

        window.modifierAdminRow = function(id, btn) {
            var tr = btn.closest('tr');
            var cells = tr.querySelectorAll('td');
            var name = cells[0].textContent.trim();
            var email = cells[1].textContent.trim();
            cells[0].innerHTML = '<input type="text" value="' + escAttr(name) + '" id="editName' + id + '" class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">';
            cells[1].innerHTML = '<input type="email" value="' + escAttr(email) + '" id="editEmail' + id + '" class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">';
            cells[2].innerHTML =
                '<button onclick="enregistrerAdmin(' + id + ')" class="text-green-600 hover:text-green-800 mr-2 text-sm">Enregistrer</button>' +
                '<button onclick="chargerAdminsPublic()" class="text-gray-500 hover:text-gray-700 text-sm">Annuler</button>';
        };

        window.chargerAdminsPublic = chargerAdmins;

        window.enregistrerAdmin = function(id) {
            var name = document.getElementById('editName' + id).value.trim();
            var email = document.getElementById('editEmail' + id).value.trim();
            if (!name || !email) { showMsg('Veuillez remplir tous les champs.', true); return; }
            fetch('/admin/admins/' + id, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ name: name, email: email })
            })
            .then(function(r) { return r.json().then(function(d) { return { ok: r.ok, data: d }; }); })
            .then(function(res) {
                if (res.ok) { showMsg(res.data.message, false); chargerAdmins(); }
                else { showMsg(res.data.error || 'Erreur.', true); }
            })
            .catch(function() { showMsg('Erreur réseau.', true); });
        };

        window.ajouterAdmin = function() {
            var name = document.getElementById('newAdminName').value.trim();
            var email = document.getElementById('newAdminEmail').value.trim();
            if (!name || !email) { showMsg('Veuillez remplir le nom et l\'email.', true); return; }
            fetch('/admin/admins', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ name: name, email: email })
            })
            .then(function(r) { return r.json().then(function(d) { return { ok: r.ok, data: d }; }); })
            .then(function(res) {
                if (res.ok) {
                    showMsg(res.data.message, false);
                    document.getElementById('newAdminName').value = '';
                    document.getElementById('newAdminEmail').value = '';
                    chargerAdmins();
                } else {
                    var msg = res.data.error || (res.data.errors ? Object.values(res.data.errors).flat().join(' ') : 'Erreur.');
                    showMsg(msg, true);
                }
            })
            .catch(function() { showMsg('Erreur réseau.', true); });
        };

        window.supprimerAdmin = function(id) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cet administrateur ?')) return;
            fetch('/admin/admins/' + id, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            })
            .then(function(r) { return r.json().then(function(d) { return { ok: r.ok, data: d }; }); })
            .then(function(res) {
                if (res.ok) { showMsg(res.data.message, false); chargerAdmins(); }
                else { showMsg(res.data.error || 'Erreur.', true); }
            })
            .catch(function() { showMsg('Erreur réseau.', true); });
        };
    })();
    </script>

    <!-- Modal génération carte -->
    <div id="modalGenerationCarte" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-5 mx-auto p-5 border w-11/12 max-w-7xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-2xl font-bold text-gray-900">Génération de la carte</h3>
                    <button onclick="document.getElementById('modalGenerationCarte').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="text-center py-6">
                    <div id="generation-carte-spinner" class="mx-auto mb-4">
                        <svg class="animate-spin h-12 w-12 text-blue-500 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </div>
                    <div id="generation-carte-success" class="hidden mx-auto mb-4">
                        <svg class="h-12 w-12 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <div id="generation-carte-error" class="hidden mx-auto mb-4">
                        <svg class="h-12 w-12 text-red-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <p id="generation-carte-status" class="text-gray-700 text-lg font-medium mb-2">Génération en cours...</p>
                    <p id="generation-carte-subtitle" class="text-gray-500 text-sm mb-4">Cette opération peut prendre quelques minutes.</p>
                    <img id="generation-carte-preview" class="hidden max-w-full rounded shadow border mt-4" alt="Carte générée">
                </div>

                <div class="flex justify-end gap-3 border-t pt-4">
                    <a id="generation-carte-download" href="{{ route('export.carte') }}" class="hidden px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded text-sm font-medium">
                        Télécharger la carte
                    </a>
                    <button onclick="document.getElementById('modalGenerationCarte').classList.add('hidden')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 text-sm font-medium">
                        Fermer
                    </button>
                </div>
            </div>
        </div>
    </div>

    @include('admin._footer')
</body>
</html>



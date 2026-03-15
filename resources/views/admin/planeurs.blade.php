<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Planeurs inscrits - Administration Wassmer Cup</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-sky-50 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Planeurs inscrits</h1>
                    <p class="text-gray-600 mt-1">{{ $totalPlaneurs }} / {{ $limitePlaneurs }} planeurs inscrits</p>
                </div>
                <div class="flex gap-4">
                    <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                        Retour au tableau de bord
                    </a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-blue-600 hover:text-blue-800">
                            Déconnexion
                        </button>
                    </form>
                </div>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            <!-- Liste des planeurs -->
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-300">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 border-b text-left">Immatriculation</th>
                            <th class="px-4 py-2 border-b text-left">Marque</th>
                            <th class="px-4 py-2 border-b text-left">Modèle</th>
                            <th class="px-4 py-2 border-b text-left">Type</th>
                            <th class="px-4 py-2 border-b text-left">Propriétaire (Responsable)</th>
                            <th class="px-4 py-2 border-b text-left">Pilotes inscrits</th>
                            <th class="px-4 py-2 border-b text-left">Statut</th>
                            <th class="px-4 py-2 border-b text-left">Date d'inscription</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($planeurs as $planeur)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b font-semibold">{{ $planeur->immatriculation }}</td>
                                <td class="px-4 py-2 border-b">{{ $planeur->marque ?? '-' }}</td>
                                <td class="px-4 py-2 border-b">{{ $planeur->modele }}</td>
                                <td class="px-4 py-2 border-b">{{ $planeur->type ? ucfirst($planeur->type) : '-' }}</td>
                                <td class="px-4 py-2 border-b">
                                    @if($planeur->piloteProprietaire)
                                        <div>
                                            <p class="font-semibold">{{ $planeur->piloteProprietaire->prenom }} {{ $planeur->piloteProprietaire->nom }}</p>
                                            <p class="text-sm text-gray-600">{{ $planeur->piloteProprietaire->email }}</p>
                                            @if($planeur->piloteProprietaire->telephone)
                                                <p class="text-sm text-gray-600">{{ $planeur->piloteProprietaire->telephone }}</p>
                                            @endif
                                        </div>
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

            <!-- Pagination -->
            @if($planeurs->hasPages())
                <div class="mt-6">
                    {{ $planeurs->links() }}
                </div>
            @endif

            <!-- Actions d'export -->
            <div class="mt-6 flex gap-4">
                <a href="{{ route('admin.export.planeurs') }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition">
                    Exporter liste des planeurs (CSV)
                </a>
            </div>
        </div>
    </div>
    @include('admin._footer')
</body>
</html>

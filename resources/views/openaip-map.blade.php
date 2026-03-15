@php
    use App\Services\OpenAipService;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Carte OpenAIP - Wassmer Cup</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Figtree', sans-serif;
            margin: 0;
            padding: 0;
        }
        #openaip-map {
            width: 100%;
            height: 600px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .map-container {
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }
        .map-header {
            margin-bottom: 20px;
        }
        .map-header h1 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .map-controls {
            margin-bottom: 15px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .btn {
            padding: 8px 16px;
            background-color: #FF2D20;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.2s;
        }
        .btn:hover {
            background-color: #d6251a;
        }
        .info-panel {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .info-panel p {
            margin: 5px 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="map-container">
        <div class="map-header">
            <h1>Carte Aéronautique OpenAIP</h1>
            <div class="info-panel">
                <p><strong>Note:</strong> Cette carte utilise les données OpenAIP pour afficher les informations aéronautiques.</p>
                @php
                    $hasKey = \App\Services\OpenAipService::hasApiKey();
                @endphp
                @if($hasKey)
                    <p style="color: green;">✓ Clé API OpenAIP configurée - Les données se chargent automatiquement sur la carte.</p>
                @else
                    <p style="color: orange;">⚠ Clé API OpenAIP non configurée. Ajoutez <code>OPENAIP_API_KEY</code> dans votre fichier .env</p>
                @endif
            </div>
        </div>

        <div class="map-controls">
            <button class="btn" onclick="centerOnFrance()">Centrer sur la France</button>
            @if($hasKey)
                <button class="btn" onclick="toggleOpenAIPTiles()">Basculer tuiles OpenAIP</button>
            @else
                <button class="btn" disabled title="Ajoutez OPENAIP_API_KEY dans votre .env">Clé API non configurée</button>
            @endif
        </div>

        <div id="openaip-map"></div>
    </div>

    {{-- La clé API n'est plus nécessaire côté client, elle est gérée par le proxy Laravel --}}

    @vite(['resources/js/openaip-map-example.js'])
</body>
</html>

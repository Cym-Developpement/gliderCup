<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Erreur de paiement - Wassmer Cup</title>
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
    </style>
</head>
<body class="min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-6xl">
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
                </div>
            </div>
        </header>

        <!-- Section d'erreur -->
        <section class="bg-white rounded-lg shadow-xl p-8 mb-8">
            <div class="text-center mb-8">
                <div class="text-6xl mb-4">❌</div>
                <h2 class="text-3xl font-bold text-red-600 mb-4">Erreur lors du paiement</h2>
                <p class="text-lg text-gray-700 mb-6">Une erreur technique s'est produite lors du traitement de votre paiement.</p>
            </div>

            <div class="bg-red-50 border-l-4 border-red-500 p-6 rounded mb-8">
                <h3 class="text-xl font-semibold text-red-900 mb-4">Que faire ?</h3>
                <ul class="list-disc list-inside space-y-2 text-red-800 text-sm">
                    <li>Vérifiez vos informations bancaires et réessayez.</li>
                    <li>Assurez-vous que votre carte bancaire est valide et dispose de fonds suffisants.</li>
                    <li>Contactez votre banque si le problème persiste.</li>
                    <li>Vous pouvez retourner à la page de paiement pour réessayer.</li>
                </ul>
            </div>

            @if($error)
            <div class="bg-gray-50 border-l-4 border-gray-400 p-4 rounded mb-8">
                <h4 class="font-semibold text-gray-900 mb-2">Détails de l'erreur :</h4>
                <p class="text-sm text-gray-700 font-mono">{{ $error }}</p>
            </div>
            @endif

            @if(isset($isTestMode) && $isTestMode)
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded mb-8">
                <p class="text-sm text-yellow-800">
                    <strong>Mode test :</strong> Cette erreur est survenue en mode test. Aucun paiement réel n'a été effectué.
                </p>
            </div>
            @endif

            <!-- Actions -->
            <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('paiement.public', ['identifiantVirement' => $pilote->identifiant_virement]) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 shadow-lg text-center">
                    Réessayer le paiement
                </a>
                @auth('pilotes')
                <a href="{{ route('dashboard') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 shadow-lg text-center">
                    Retour au tableau de bord
                </a>
                @endauth
                <a href="{{ route('inscription.index') }}" class="bg-gray-400 hover:bg-gray-500 text-white font-bold py-3 px-6 rounded-lg transition duration-200 shadow-lg text-center">
                    Retour à l'accueil
                </a>
            </div>
        </section>
    </div>
</body>
</html>

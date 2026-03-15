<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Paiement - Wassmer Cup</title>
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
        .paiement-card {
            transition: all 0.3s ease;
        }
        .paiement-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        .paiement-card.selected {
            border-color: #3b82f6;
            border-width: 2px;
            background-color: #eff6ff;
        }
        
        /* Styles HelloAsso Button */
        .HaPay {
            width: 100%;
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            -webkit-box-pack: center;
            -ms-flex-pack: center;
        }

        .HaPay * {
            font-family: "Open Sans", "Trebuchet MS", "Lucida Sans Unicode",
                "Lucida Grande", "Lucida Sans", Arial, sans-serif;
            transition: all 0.3s ease-out;
        }

        .HaPayButton {
            align-items: stretch;
            -webkit-box-pack: stretch;
            -ms-flex-pack: stretch;
            background: none;
            border: none;
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            padding: 0;
            border-radius: 8px;
            width: 100%;
        }

        .HaPayButton:hover {
            cursor: pointer;
        }

        .HaPayButton:not(:disabled):focus {
            box-shadow: 0 0 0 0.25rem rgba(73, 211, 138, 0.25);
            -webkit-box-shadow: 0 0 0 0.25rem rgba(73, 211, 138, 0.25);
        }

        .HaPayButton:not(:disabled):hover .HaPayButtonLabel,
        .HaPayButton:not(:disabled):focus .HaPayButtonLabel {
            background-color: #483dbe;
        }

        .HaPayButton:not(:disabled):hover .HaPayButtonLogo,
        .HaPayButton:not(:disabled):focus .HaPayButtonLogo,
        .HaPayButton:not(:disabled):hover .HaPayButtonLabel,
        .HaPayButton:not(:disabled):focus .HaPayButtonLabel {
            border: 1px solid #483dbe;
        }

        .HaPayButton:disabled {
            cursor: not-allowed;
        }

        .HaPayButton:disabled .HaPayButtonLogo,
        .HaPayButton:disabled .HaPayButtonLabel {
            border: 1px solid #d1d6de;
        }

        .HaPayButtonLogo {
            background-color: #ffffff;
            border: 1px solid #4c40cf;
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
            padding: 10px 16px;
            width: 30%;
        }

        .HaPayButtonLabel {
            align-items: center;
            -webkit-box-pack: center;
            -ms-flex-pack: center;
            justify-content: space-between;
            column-gap: 5px;
            background-color: #4c40cf;
            border: 1px solid #4c40cf;
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
            color: #ffffff;
            font-size: 16px;
            font-weight: 800;
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            padding: 0 16px;
        }

        .HaPayButton:disabled .HaPayButtonLabel {
            background-color: #d1d6de;
            color: #505870;
        }

        .HaPaySecured {
            align-items: center;
            -webkit-box-pack: center;
            -ms-flex-pack: center;
            justify-content: space-between;
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            column-gap: 5px;
            padding: 8px 16px;
            font-size: 12px;
            font-weight: 600;
            color: #2e2f5e;
        }

        .HaPay svg {
            fill: currentColor;
        }
    </style>
</head>
<body class="min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-6xl">
        @if(isset($useSandbox) && $useSandbox)
        <!-- Indicateur de mode sandbox -->
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6 rounded" role="alert">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="font-bold">Mode TEST/SANDBOX activé</p>
                    <p class="text-sm">Cette page utilise l'environnement de test HelloAsso. Les paiements ne seront pas réels.</p>
                </div>
            </div>
        </div>
        @endif
        
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

        <!-- Messages de succès/erreur -->
        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-md">
                <p class="font-bold">{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-md">
                <p class="font-bold">{{ session('error') }}</p>
            </div>
        @endif

        <!-- Indicateur de mode test -->
        @if(isset($isTestMode) && $isTestMode)
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 p-4 mb-6 rounded shadow-md">
                <p class="font-bold">🧪 PAGE DE TEST - Paiement HelloAsso à 1€</p>
                <p class="text-sm mt-1">Cette page est destinée aux tests de paiement. Le montant est fixé à 1,00 €.</p>
                @if(isset($useSandbox) && $useSandbox)
                    <p class="text-sm mt-2 font-semibold">⚠️ Serveur de test (SANDBOX) - Aucun paiement réel ne sera effectué</p>
                    
                    <div class="mt-4 p-3 bg-yellow-200 rounded border border-yellow-400">
                        <p class="font-bold text-sm mb-2">💳 Carte bancaire de test à utiliser :</p>
                        <div class="text-xs space-y-1">
                            <p><span class="font-semibold">Type :</span> Visa</p>
                            <p><span class="font-semibold">Numéro :</span> <code class="bg-white px-2 py-1 rounded">4242424242424242</code></p>
                            <p><span class="font-semibold">CVV :</span> 3 chiffres (au choix, ex: 123)</p>
                            <p><span class="font-semibold">Date d'expiration :</span> Toute date future (ex: 12/25)</p>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <!-- Section de paiement -->
        <section class="bg-white rounded-lg shadow-xl p-8 mb-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-6 text-center">
                @if(isset($isTestMode) && $isTestMode)
                    Paiement de test - HelloAsso
                @else
                    Paiement de votre inscription
                @endif
            </h2>
            
            <!-- Récapitulatif -->
            <div class="bg-gray-50 rounded-lg p-6 mb-8">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Récapitulatif de votre inscription</h3>
                <div class="space-y-2 text-gray-700">
                    <div class="flex justify-between">
                        <span>Pilote :</span>
                        <span class="font-semibold">{{ $pilote->prenom }} {{ $pilote->nom }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Email :</span>
                        <span class="font-semibold">{{ $pilote->email }}</span>
                    </div>
                    @if($pilote->identifiant_virement)
                    <div class="flex justify-between">
                        <span>Identifiant de paiement :</span>
                        <span class="font-semibold font-mono text-blue-600">{{ $pilote->identifiant_virement }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <span>Nombre de planeurs :</span>
                        <span class="font-semibold">{{ $nombrePlaneurs }}</span>
                    </div>
                    <div class="border-t border-gray-300 pt-2 mt-2">
                        <div class="flex justify-between text-sm text-gray-600 mb-1">
                            <span>Adhésion ({{ $nombrePlaneurs }} pilote{{ $nombrePlaneurs > 1 ? 's' : '' }})</span>
                            <span>{{ number_format($montantAdhesion, 2, ',', ' ') }} €</span>
                        </div>
                        @if($nombrePlaneurs > 0)
                        <div class="flex justify-between text-sm text-gray-600 mb-1">
                            <span>Planeur{{ $nombrePlaneurs > 1 ? 's' : '' }} ({{ $nombrePlaneurs }} × {{ number_format($montantPlaneur, 2, ',', ' ') }} €)</span>
                            <span>{{ number_format($nombrePlaneurs * $montantPlaneur, 2, ',', ' ') }} €</span>
                        </div>
                        @endif
                        <div class="flex justify-between text-lg font-bold text-gray-900 mt-3 pt-3 border-t-2 border-gray-400">
                            <span>Total à payer :</span>
                            <span>{{ number_format($montantTotal, 2, ',', ' ') }} €</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Options de paiement -->
            <h3 class="text-2xl font-semibold text-gray-800 mb-6 text-center">Choisissez votre mode de paiement</h3>
            
            <div class="grid md:grid-cols-3 gap-6 mb-8">
                <!-- Option 1 : Chèque -->
                <div class="paiement-card bg-white border-2 border-gray-300 rounded-lg p-6 cursor-pointer" onclick="selectPaymentMethod('cheque')">
                    <div class="text-center mb-4">
                        <div class="text-4xl mb-2">📝</div>
                        <h4 class="text-xl font-bold text-gray-800 mb-2">Chèque</h4>
                    </div>
                    <div class="text-sm text-gray-600 space-y-2">
                        <p class="font-semibold">Envoyez votre chèque à l'adresse suivante :</p>
                        @if($adresseCheque)
                            <p class="whitespace-pre-line">{{ $adresseCheque }}</p>
                        @else
                            <p class="text-gray-400 italic">Adresse à configurer dans .env</p>
                        @endif
                        <p class="mt-4 font-semibold">Montant : <span class="text-lg text-gray-900">{{ number_format($montantTotal, 2, ',', ' ') }} €</span></p>
                    </div>
                </div>

                <!-- Option 2 : Virement -->
                <div class="paiement-card bg-white border-2 border-gray-300 rounded-lg p-6 cursor-pointer" onclick="selectPaymentMethod('virement')">
                    <div class="text-center mb-4">
                        <div class="text-4xl mb-2">🏦</div>
                        <h4 class="text-xl font-bold text-gray-800 mb-2">Virement bancaire</h4>
                    </div>
                    <div class="text-sm text-gray-600 space-y-2">
                        <div>
                            <p class="font-mono text-xs break-all">IBAN : FR76 1333 5004 0108 9253 9002 919</p>
                        </div>
                        @if($bicVirement)
                        <div class="mt-2">
                            <p class="font-semibold mb-1">BIC :</p>
                            <p class="font-mono text-xs">{{ $bicVirement }}</p>
                        </div>
                        @endif
                        <div class="mt-2">
                            <p class="font-semibold mb-1">Référence à indiquer :</p>
                            <p class="font-mono text-xs bg-yellow-50 p-2 rounded">{{ $referenceVirement }}</p>
                        </div>
                        <p class="mt-4 font-semibold">Montant : <span class="text-lg text-gray-900">{{ number_format($montantTotal, 2, ',', ' ') }} €</span></p>
                    </div>
                </div>

                <!-- Option 3 : Hello Asso -->
                <div class="paiement-card bg-white border-2 border-gray-300 rounded-lg p-6 cursor-pointer" onclick="selectPaymentMethod('helloasso')">
                    <div class="text-center mb-4">
                        <div class="text-4xl mb-2">💳</div>
                        <h4 class="text-xl font-bold text-gray-800 mb-2">Paiement en ligne</h4>
                        <p class="text-sm text-gray-500">via Hello Asso</p>
                    </div>
                    <div class="text-sm text-gray-600 space-y-2">
                        <p>Paiement sécurisé par carte bancaire via Hello Asso Checkout.</p>
                        <p class="mt-4 font-semibold">Montant : <span class="text-lg text-gray-900">{{ number_format($montantTotal, 2, ',', ' ') }} €</span></p>
                        
                        <!-- Explication sur la contribution volontaire -->
                        <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded text-xs text-blue-800">
                            <p class="mb-2"><strong>Le modèle solidaire de HelloAsso</strong></p>
                            <p>100% de votre paiement sera versé à l'association. Vous pouvez soutenir HelloAsso en laissant une contribution volontaire au moment de votre paiement.</p>
                        </div>
                        
                        @if($helloAssoCheckoutUrl)
                            <div class="mt-4">
                                <div class="HaPay">
                                    <button class="HaPayButton" onclick="window.location.href='{{ $helloAssoCheckoutUrl }}'; return false;" style="cursor: pointer;">
                                        <img
                                            src="https://api.helloasso.com/v5/img/logo-ha.svg"
                                            alt=""
                                            class="HaPayButtonLogo"
                                        />
                                        <div class="HaPayButtonLabel">
                                            <span> Payer avec </span>
                                            <svg
                                                width="73"
                                                height="14"
                                                viewBox="0 0 73 14"
                                                fill="none"
                                                xmlns="http://www.w3.org/2000/svg"
                                            >
                                                <path
                                                    d="M72.9992 8.78692C72.9992 11.7371 71.242 13.6283 68.4005 13.6283C65.5964 13.6283 63.8018 11.9073 63.8018 8.74909C63.8018 5.79888 65.559 3.90771 68.4005 3.90771C71.2046 3.90771 72.9992 5.64759 72.9992 8.78692ZM67.2041 8.74909C67.2041 10.5457 67.5779 11.2265 68.4005 11.2265C69.223 11.2265 69.5969 10.5079 69.5969 8.78692C69.5969 6.99031 69.223 6.30949 68.4005 6.30949C67.5779 6.30949 67.1854 7.04705 67.2041 8.74909Z"
                                                />
                                                <path
                                                    d="M62.978 5.08045L61.8003 6.89597C61.1647 6.47991 60.4356 6.25297 59.6692 6.23406C59.1084 6.23406 58.9214 6.40426 58.9214 6.65011C58.9214 6.9527 59.0149 7.08508 60.716 7.61461C62.4172 8.14413 63.3332 8.88169 63.3332 10.527C63.3332 12.3803 61.576 13.6474 59.1084 13.6474C57.5381 13.6474 56.0986 13.0801 55.1826 12.2101L56.7529 10.4514C57.3885 10.962 58.211 11.3402 59.0336 11.3402C59.6131 11.3402 59.9683 11.1511 59.9683 10.7918C59.9683 10.3568 59.7813 10.2622 58.2484 9.78945C56.5847 9.27883 55.65 8.31434 55.65 6.85814C55.65 5.23174 57.0333 3.92684 59.5383 3.92684C60.8656 3.90793 62.1555 4.36181 62.978 5.08045Z"
                                                />
                                                <path
                                                    d="M54.7358 5.08045L53.5581 6.89597C52.9225 6.47991 52.1934 6.25297 51.427 6.23406C50.8662 6.23406 50.6792 6.40426 50.6792 6.65011C50.6792 6.9527 50.7727 7.08508 52.4738 7.61461C54.175 8.14413 55.091 8.88169 55.091 10.527C55.091 12.3803 53.3338 13.6474 50.8662 13.6474C49.2959 13.6474 47.8564 13.0801 46.9404 12.2101L48.5107 10.4514C49.1463 10.962 49.9689 11.3402 50.7914 11.3402C51.3709 11.3402 51.7261 11.1511 51.7261 10.7918C51.7261 10.3568 51.5391 10.2622 50.0062 9.78945C48.3238 9.27883 47.4078 8.31434 47.4078 6.85814C47.4078 5.23174 48.7911 3.92684 51.2961 3.92684C52.6234 3.90793 53.9133 4.36181 54.7358 5.08045Z"
                                                />
                                                <path
                                                    d="M46.7721 11.4156L46.0991 13.5526C44.9401 13.477 44.1923 13.1555 43.6876 12.3045C43.0333 13.3068 42.0051 13.6283 40.9956 13.6283C39.201 13.6283 38.042 12.418 38.042 10.7537C38.042 8.74909 39.5375 7.65222 42.3603 7.65222H42.9959V7.42528C42.9959 6.51752 42.6968 6.27167 41.706 6.27167C40.9209 6.30949 40.1357 6.4797 39.4067 6.74446L38.6963 4.62636C39.8179 4.17248 41.0143 3.94554 42.2294 3.90771C45.0709 3.90771 46.23 5.00459 46.23 7.23616V10.3566C46.23 10.9996 46.3795 11.2643 46.7721 11.4156ZM43.0146 10.7348V9.39209H42.6594C41.7247 9.39209 41.2947 9.71359 41.2947 10.4133C41.2947 10.9239 41.5752 11.2643 42.0238 11.2643C42.4164 11.2643 42.7903 11.0563 43.0146 10.7348Z"
                                                />
                                                <path
                                                    d="M37.5363 8.78692C37.5363 11.7371 35.7791 13.6283 32.9376 13.6283C30.1335 13.6283 28.3389 11.9073 28.3389 8.74909C28.3389 5.79888 30.0961 3.90771 32.9376 3.90771C35.7417 3.90771 37.5363 5.64759 37.5363 8.78692ZM31.7412 8.74909C31.7412 10.5457 32.1151 11.2265 32.9376 11.2265C33.7601 11.2265 34.134 10.5079 34.134 8.78692C34.134 6.99031 33.7601 6.30949 32.9376 6.30949C32.1151 6.30949 31.7225 7.04705 31.7412 8.74909Z"
                                                />
                                                <path
                                                    d="M23.8154 10.6972V0.692948L27.1243 0.352539V10.527C27.1243 10.8296 27.2551 10.9809 27.5355 10.9809C27.6477 10.9809 27.7786 10.962 27.8907 10.9052L28.4889 13.2881C27.8907 13.4961 27.2738 13.6096 26.6569 13.5907C24.8249 13.6285 23.8154 12.5505 23.8154 10.6972Z"
                                                />
                                                <path
                                                    d="M18.8057 10.6972V0.692948L22.1145 0.352539V10.527C22.1145 10.8296 22.2454 10.9809 22.5071 10.9809C22.6192 10.9809 22.7501 10.962 22.8623 10.9052L23.4418 13.2881C22.8436 13.4961 22.2267 13.6096 21.6098 13.5907C19.8151 13.6285 18.8057 12.5505 18.8057 10.6972Z"
                                                />
                                                <path
                                                    d="M17.9071 9.71359H12.4859C12.6728 11.0185 13.3084 11.2454 14.2805 11.2454C14.9161 11.2454 15.533 10.9807 16.2994 10.4511L17.6454 12.2856C16.6172 13.1555 15.3087 13.6283 13.9627 13.6283C10.6912 13.6283 9.13965 11.5858 9.13965 8.78692C9.13965 6.13929 10.6352 3.90771 13.5888 3.90771C16.2247 3.90771 17.9632 5.60976 17.9632 8.63562C17.9819 8.93821 17.9445 9.39209 17.9071 9.71359ZM14.7291 7.70895C14.7105 6.80119 14.5235 6.04473 13.6823 6.04473C12.9719 6.04473 12.6167 6.46079 12.4859 7.84134H14.7291V7.70895Z"
                                                />
                                                <path
                                                    d="M8.24307 6.61229V13.2692H4.93423V7.21746C4.93423 6.49882 4.7286 6.32862 4.4295 6.32862C4.07431 6.32862 3.70043 6.61229 3.30786 7.21746V13.2503H-0.000976562V0.692948L3.30786 0.352539V5.06154C4.07431 4.24834 4.82207 3.90793 5.83154 3.90793C7.32706 3.90793 8.24307 4.89133 8.24307 6.61229Z"
                                                />
                                            </svg>
                                        </div>
                                    </button>
                                    <div class="HaPaySecured">
                                        <svg
                                            width="9"
                                            height="10"
                                            viewBox="0 0 11 12"
                                            fill="none"
                                            xmlns="http://www.w3.org/2000/svg"
                                        >
                                            <path
                                                d="M3.875 3V4.5H7.625V3C7.625 1.96875 6.78125 1.125 5.75 1.125C4.69531 1.125 3.875 1.96875 3.875 3ZM2.75 4.5V3C2.75 1.35938 4.08594 0 5.75 0C7.39062 0 8.75 1.35938 8.75 3V4.5H9.5C10.3203 4.5 11 5.17969 11 6V10.5C11 11.3438 10.3203 12 9.5 12H2C1.15625 12 0.5 11.3438 0.5 10.5V6C0.5 5.17969 1.15625 4.5 2 4.5H2.75ZM1.625 6V10.5C1.625 10.7109 1.78906 10.875 2 10.875H9.5C9.6875 10.875 9.875 10.7109 9.875 10.5V6C9.875 5.8125 9.6875 5.625 9.5 5.625H2C1.78906 5.625 1.625 5.8125 1.625 6Z"
                                            />
                                        </svg>
                                        <span>Paiement sécurisé</span>
                                        <img
                                            src="https://helloassodocumentsprod.blob.core.windows.net/public-documents/bouton_payer_avec_helloasso/logo-visa.svg"
                                            alt="Logo Visa"
                                        />
                                        <img
                                            src="https://helloassodocumentsprod.blob.core.windows.net/public-documents/bouton_payer_avec_helloasso/logo-mastercard.svg"
                                            alt="Logo Mastercard"
                                        />
                                        <img
                                            src="https://helloassodocumentsprod.blob.core.windows.net/public-documents/bouton_payer_avec_helloasso/logo-cb.svg"
                                            alt="Logo CB"
                                        />
                                        <img
                                            src="https://helloassodocumentsprod.blob.core.windows.net/public-documents/bouton_payer_avec_helloasso/logo-pci.svg"
                                            alt="Logo PCI"
                                        />
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-sm text-yellow-800">
                                <p class="font-semibold mb-2">⚠️ Paiement HelloAsso indisponible</p>
                                <p>Le paiement par carte bancaire n'est pas disponible pour le moment.</p>
                                @if(isset($isTestMode) && $isTestMode)
                                    <p class="mt-2 text-xs">Mode test : Vérifiez les variables d'environnement HELLOASSO_CLIENT_ID, HELLOASSO_CLIENT_SECRET et HELLOASSO_ORG_SLUG dans votre fichier .env</p>
                                @else
                                    <p class="mt-2">Veuillez utiliser le chèque ou le virement bancaire.</p>
                                @endif
                                @if(isset($helloAssoError))
                                    <p class="mt-2 text-xs italic">Erreur : {{ $helloAssoError }}</p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Instructions -->
            <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded">
                <h4 class="text-lg font-semibold text-blue-900 mb-2">Important</h4>
                <ul class="list-disc list-inside space-y-2 text-blue-800 text-sm">
                    <li>Votre inscription est en attente de validation par un administrateur.</li>
                    <li>Le paiement doit être effectué dans les meilleurs délais pour finaliser votre inscription.</li>
                    <li>Pour le virement, n'oubliez pas d'indiquer la référence : <strong>{{ $referenceVirement }}</strong></li>
                    <li>Vous recevrez une confirmation par email une fois votre inscription validée.</li>
                </ul>
            </div>

            <!-- Actions -->
            <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
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

    <script>
        function selectPaymentMethod(method) {
            // Retirer la classe selected de toutes les cartes
            document.querySelectorAll('.paiement-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Ajouter la classe selected à la carte cliquée
            event.currentTarget.classList.add('selected');
        }
    </script>
</body>
</html>

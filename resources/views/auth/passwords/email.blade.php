<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mot de passe oublié - Wassmer Cup</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Figtree', sans-serif;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-sky-50 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-md">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-6 text-center">Wassmer Cup</h1>
            <h2 class="text-2xl font-semibold text-gray-800 mb-6 text-center">Réinitialiser le mot de passe</h2>

            @if (session('status'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                    {{ session('status') }}
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <p class="text-gray-600 mb-6">
                Entrez votre adresse e-mail et nous vous enverrons un lien pour réinitialiser votre mot de passe.
            </p>

            <form action="{{ route('password.email') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        E-mail <span class="text-red-500">*</span>
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="{{ old('email') }}"
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <button type="submit" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 shadow-lg hover:shadow-xl">
                    Envoyer le lien de réinitialisation
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-800">
                    Retour à la connexion
                </a>
            </div>
        </div>
    </div>
</body>
</html>


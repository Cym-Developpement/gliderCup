<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion - Wassmer Cup</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
            <h2 class="text-2xl font-semibold text-gray-800 mb-6 text-center">Connexion</h2>

            @if($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-6">
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

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Mot de passe <span class="text-red-500">*</span>
                    </label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="flex items-center">
                    <input type="checkbox" 
                           id="remember" 
                           name="remember" 
                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="remember" class="ml-2 text-sm text-gray-700">
                        Se souvenir de moi
                    </label>
                </div>

                <button type="submit" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 shadow-lg hover:shadow-xl">
                    Se connecter
                </button>
            </form>

            <div class="mt-6 space-y-2 text-center">
                <div>
                    <a href="{{ route('password.request') }}" class="text-blue-600 hover:text-blue-800">
                        Mot de passe oublié ?
                    </a>
                </div>
                <div>
                    <a href="{{ route('inscription.index') }}" class="text-blue-600 hover:text-blue-800">
                        Retour à l'inscription
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>


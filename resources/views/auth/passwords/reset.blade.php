<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Réinitialiser le mot de passe - Wassmer Cup</title>
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

            <form action="{{ route('password.update') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        E-mail <span class="text-red-500">*</span>
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="{{ $email }}"
                           required
                           readonly
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Nouveau mot de passe <span class="text-red-500">*</span>
                    </label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           required
                           minlength="8"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="mt-1 text-sm text-gray-500">
                        Minimum 8 caractères
                    </p>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                        Confirmer le mot de passe <span class="text-red-500">*</span>
                    </label>
                    <input type="password" 
                           id="password_confirmation" 
                           name="password_confirmation" 
                           required
                           minlength="8"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <button type="submit" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 shadow-lg hover:shadow-xl">
                    Réinitialiser le mot de passe
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


<?php
/**
 * Script d'installation de l'authentification HTTP Basic Apache
 * 
 * Ce script génère un identifiant et un mot de passe pour protéger
 * le formulaire d'inscription en version beta.
 * 
 * IMPORTANT : Supprimez ce fichier après l'installation pour des raisons de sécurité.
 */

// Configuration
$htpasswdFile = __DIR__ . '/.htpasswd';
$htaccessFile = __DIR__ . '/.htaccess';
$realm = 'Wassmer Cup - Version Beta';

// Génération aléatoire de l'identifiant et du mot de passe
function generateRandomString($length = 12) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

// Génération des identifiants
$username = 'beta_' . generateRandomString(8);
$password = generateRandomString(16);

// Hash du mot de passe pour .htpasswd (format Apache)
function createHtpasswdHash($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

$passwordHash = createHtpasswdHash($password);

// Création du fichier .htpasswd
$htpasswdContent = $username . ':' . $passwordHash . "\n";

if (file_put_contents($htpasswdFile, $htpasswdContent) === false) {
    die('Erreur : Impossible de créer le fichier .htpasswd');
}

// Lecture du fichier .htaccess actuel
$htaccessContent = file_get_contents($htaccessFile);
if ($htaccessContent === false) {
    die('Erreur : Impossible de lire le fichier .htaccess');
}

// Configuration de l'authentification HTTP Basic
// Protection de toutes les routes (AVANT les règles de réécriture)
$authConfig = <<<'AUTH'

# Authentification HTTP Basic pour la version beta (AVANT les règles de réécriture)
<IfModule mod_auth_basic.c>
    # Protéger toutes les requêtes
    AuthType Basic
    AuthName "Wassmer Cup - Version Beta"
    AuthUserFile AUTH_FILE_PATH
    Require valid-user
</IfModule>
AUTH;

// Remplacement du chemin du fichier .htpasswd par le chemin absolu
$authConfig = str_replace('AUTH_FILE_PATH', $htpasswdFile, $authConfig);

// Vérifier si l'authentification n'est pas déjà configurée
if (strpos($htaccessContent, '# Authentification HTTP Basic pour la version beta') !== false) {
    // Remplacer la configuration existante
    $pattern = '/# Authentification HTTP Basic pour la version beta.*?<\/IfModule>/s';
    $htaccessContent = preg_replace($pattern, trim($authConfig), $htaccessContent);
} else {
    // Insérer la configuration AVANT les règles de réécriture (avant <IfModule mod_rewrite.c>)
    if (preg_match('/(<IfModule mod_rewrite\.c>)/', $htaccessContent, $matches)) {
        $htaccessContent = str_replace(
            $matches[1],
            trim($authConfig) . "\n\n" . $matches[1],
            $htaccessContent
        );
    } else {
        // Si pas de mod_rewrite, ajouter à la fin
        $htaccessContent = rtrim($htaccessContent) . "\n\n" . trim($authConfig) . "\n";
    }
}

// Écriture du fichier .htaccess mis à jour
if (file_put_contents($htaccessFile, $htaccessContent) === false) {
    die('Erreur : Impossible de mettre à jour le fichier .htaccess');
}

// Affichage des identifiants
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation Auth Basic - Wassmer Cup</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #007bff;
            padding-bottom: 10px;
        }
        .credentials {
            background: #f8f9fa;
            border: 2px solid #007bff;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
        }
        .credential-item {
            margin: 10px 0;
            font-size: 16px;
        }
        .label {
            font-weight: bold;
            color: #555;
            display: inline-block;
            width: 120px;
        }
        .value {
            font-family: 'Courier New', monospace;
            background: #fff;
            padding: 5px 10px;
            border-radius: 3px;
            color: #007bff;
            font-weight: bold;
        }
        .warning {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
            color: #856404;
        }
        .warning strong {
            color: #d9534f;
        }
        .success {
            background: #d4edda;
            border: 2px solid #28a745;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
            color: #155724;
        }
        .info {
            background: #d1ecf1;
            border: 2px solid #17a2b8;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
            color: #0c5460;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>✅ Installation réussie</h1>
        
        <div class="success">
            <strong>L'authentification HTTP Basic a été configurée avec succès !</strong>
        </div>
        
        <div class="credentials">
            <h2>Identifiants générés :</h2>
            <div class="credential-item">
                <span class="label">Identifiant :</span>
                <span class="value"><?php echo htmlspecialchars($username); ?></span>
            </div>
            <div class="credential-item">
                <span class="label">Mot de passe :</span>
                <span class="value"><?php echo htmlspecialchars($password); ?></span>
            </div>
        </div>
        
        <div class="warning">
            <strong>⚠️ IMPORTANT :</strong>
            <ul>
                <li>Notez ces identifiants dans un endroit sûr</li>
                <li>Le formulaire d'inscription est maintenant protégé par une authentification HTTP Basic</li>
                <li>Vous devrez entrer ces identifiants pour accéder au formulaire</li>
            </ul>
        </div>
        
        <div class="info">
            <strong>ℹ️ Informations :</strong>
            <ul>
                <li>Le fichier <code>.htpasswd</code> a été créé dans le dossier <code>public</code></li>
                <li>Le fichier <code>.htaccess</code> a été mis à jour pour activer l'authentification</li>
                <li>Seule la route d'inscription (<code>/</code>) est protégée</li>
                <li>Les autres routes (login, dashboard, admin) restent accessibles normalement</li>
            </ul>
        </div>
        
        <div class="warning">
            <strong>🔒 Sécurité :</strong>
            <p>Pour des raisons de sécurité, <strong>supprimez ce fichier</strong> (<code>install_auth.php</code>) après avoir noté les identifiants.</p>
        </div>
        
        <div style="margin-top: 30px; text-align: center;">
            <a href="/" style="display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;">
                Accéder au formulaire d'inscription
            </a>
        </div>
    </div>
</body>
</html>


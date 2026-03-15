# Wassmer Cup - Application d'inscription

Application Laravel 11 pour l'inscription à une compétition de planeur.

## Fonctionnalités

- **Page unique (One-page)** avec description de l'événement et formulaire d'inscription
- **Inscription de pilote** avec informations personnelles (nom, prénom, qualité, date de naissance, etc.)
- **Inscription de planeur** (optionnelle) - possibilité de sélectionner un planeur existant ou d'en inscrire un nouveau
- **Système d'authentification** pour les participants et les administrateurs
- **Espace participant** : chaque participant peut se connecter pour voir son inscription et son statut
- **Espace administrateur** : validation/refus des inscriptions, export des listes
- **Base de données SQLite** pour le stockage des données
- **Confirmation par e-mail** automatique après inscription (statut en attente)
- **Validation des inscriptions** par un administrateur
- **Limite de 15 planeurs** maximum pour l'événement

## Installation

1. Cloner le projet ou naviguer dans le répertoire
2. Installer les dépendances :
```bash
composer install
```

3. Configurer l'environnement :
```bash
cp .env.example .env
php artisan key:generate
```

4. La base de données SQLite est déjà configurée dans `.env` :
```
DB_CONNECTION=sqlite
```

5. Exécuter les migrations :
```bash
php artisan migrate
```

## Configuration de l'e-mail

Pour que les e-mails de confirmation fonctionnent, configurez les paramètres SMTP dans le fichier `.env` :

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=votre_username
MAIL_PASSWORD=votre_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@wassmercup.com
MAIL_FROM_NAME="${APP_NAME}"

# Adresse email de l'administrateur pour recevoir les notifications de nouvelles inscriptions
ADMIN_EMAIL=admin@wassmercup.com
```

Pour le développement, vous pouvez utiliser [Mailtrap](https://mailtrap.io/) ou configurer un serveur SMTP local.

## Utilisation

### Pour les participants

1. Démarrer le serveur de développement :
```bash
php artisan serve
```

2. Accéder à l'application :
```
http://localhost:8000
```

3. Remplir le formulaire d'inscription avec :
   - Les informations du pilote (nom, prénom, qualité, date de naissance, e-mail, mot de passe obligatoires)
   - Optionnellement, inscrire un planeur (sélectionner un existant ou en créer un nouveau)
   - Si mineur (< 18 ans), télécharger l'autorisation parentale signée

4. Après soumission, l'inscription est en attente de validation. Un e-mail de confirmation est envoyé.

5. Se connecter pour voir son inscription :
   - Aller sur `/login`
   - Sélectionner "Participant"
   - Utiliser l'e-mail et le mot de passe définis lors de l'inscription

### Pour les administrateurs

1. Créer un compte administrateur :
```bash
php artisan admin:create
```

2. Se connecter :
   - Aller sur `/login`
   - Sélectionner "Administrateur"
   - Utiliser les identifiants créés

3. Dans l'espace admin (`/admin/dashboard`) :
   - Voir toutes les inscriptions avec leur statut
   - Valider ou refuser les inscriptions en attente
   - Exporter la liste des pilotes (CSV)
   - Exporter la liste des planeurs (CSV)

## Structure de la base de données

### Table `pilotes`
- id
- nom
- prenom
- qualite
- date_naissance
- email (unique)
- password (hashé)
- telephone
- licence
- club
- autorisation_parentale (chemin du fichier)
- statut (en_attente, validee, refusee)
- remember_token
- created_at
- updated_at

### Table `planeurs`
- id
- pilote_id (foreign key vers pilotes - propriétaire)
- modele
- marque
- type (plastique, bois & toiles)
- immatriculation (unique)
- created_at
- updated_at

### Table `pilote_planeur` (pivot)
- id
- pilote_id (foreign key vers pilotes)
- planeur_id (foreign key vers planeurs)
- created_at
- updated_at

### Table `users` (administrateurs)
- id
- name
- email (unique)
- password (hashé)
- role (admin)
- email_verified_at
- remember_token
- created_at
- updated_at

## Technologies utilisées

- Laravel 11
- PHP 8.2+
- SQLite
- Blade (templates)
- Tailwind CSS (via CDN dans la vue)

## Routes principales

- `/` - Page d'inscription (GET/POST)
- `/login` - Connexion (GET/POST)
- `/logout` - Déconnexion (POST)
- `/dashboard` - Tableau de bord participant (protégé)
- `/admin/dashboard` - Tableau de bord administrateur (protégé)
- `/admin/inscriptions/{id}/valider` - Valider une inscription (POST)
- `/admin/inscriptions/{id}/refuser` - Refuser une inscription (POST)
- `/admin/export/pilotes` - Exporter la liste des pilotes (CSV)
- `/admin/export/planeurs` - Exporter la liste des planeurs (CSV)

## Commandes artisan

- `php artisan admin:create` - Créer un compte administrateur
- `php artisan db:clear` - Vider toutes les tables de la base de données (demande confirmation)
- `php artisan db:clear --force` - Vider toutes les tables sans confirmation
- `php artisan email:test-admin` - Envoyer un email de test à l'administrateur (utilise ADMIN_EMAIL du .env)
- `php artisan email:test-admin --email=test@example.com` - Envoyer un email de test à une adresse spécifique

## Notes

- L'application utilise SQLite par défaut pour simplifier le déploiement
- Les e-mails sont envoyés de manière synchrone (non mis en file d'attente)
- Pour la production, configurez un vrai serveur SMTP et considérez l'utilisation de queues pour les e-mails
- Les participants doivent avoir au moins 14 ans pour s'inscrire
- Les mineurs (< 18 ans) doivent fournir une autorisation parentale signée
- Les inscriptions sont créées avec le statut "en_attente" et doivent être validées par un administrateur
- La limite de 15 planeurs est vérifiée lors de l'inscription d'un nouveau planeur

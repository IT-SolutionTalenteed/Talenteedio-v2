# Configuration API Laravel avec Sanctum et Spatie Permission

## Installation

1. Installer les dépendances :
```bash
composer install
```

2. Configurer l'environnement :
```bash
cp .env.example .env
php artisan key:generate
```

3. Configurer la base de données dans `.env`

4. Exécuter les migrations et seeders :
```bash
php artisan migrate
php artisan db:seed
```

## Utilisateurs de test créés

- **Admin** : admin@example.com / password123
- **Talent** : talent@example.com / password123  
- **Entreprise** : entreprise@example.com / password123

## Endpoints API

### Authentification

#### Inscription
```
POST /api/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "talent"
}
```

#### Connexion
```
POST /api/login
Content-Type: application/json

{
    "email": "admin@example.com",
    "password": "password123"
}
```

#### Déconnexion
```
POST /api/logout
Authorization: Bearer {token}
```

#### Profil utilisateur
```
GET /api/user
Authorization: Bearer {token}
```

### Routes protégées par rôle

#### Admin
```
GET /api/admin/dashboard
Authorization: Bearer {admin_token}
```

#### Talent
```
GET /api/talent/dashboard
Authorization: Bearer {talent_token}
```

#### Entreprise
```
GET /api/entreprise/dashboard
Authorization: Bearer {entreprise_token}
```

## Rôles et Permissions

### Rôles disponibles
- `admin` : Accès complet
- `talent` : Profil, candidatures
- `entreprise` : Profil, gestion des offres

### Permissions par rôle

**Admin :**
- manage_users, manage_roles, view_dashboard
- create_profile, edit_profile, delete_profile, view_profiles
- create_job, edit_job, delete_job, view_jobs
- apply_job, manage_applications

**Talent :**
- view_dashboard, create_profile, edit_profile, view_profiles
- view_jobs, apply_job

**Entreprise :**
- view_dashboard, create_profile, edit_profile, view_profiles
- create_job, edit_job, delete_job, view_jobs, manage_applications

## Utilisation dans le code

### Vérifier un rôle
```php
if ($user->hasRole('admin')) {
    // Code pour admin
}
```

### Vérifier une permission
```php
if ($user->can('manage_users')) {
    // Code pour gérer les utilisateurs
}
```

### Middleware de rôle
```php
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Routes admin
});
```

## Structure des fichiers créés/modifiés

- `app/Models/User.php` - Modèle utilisateur avec Sanctum et Spatie
- `app/Http/Controllers/AuthController.php` - Contrôleur d'authentification
- `app/Http/Middleware/CheckRole.php` - Middleware de vérification des rôles
- `database/seeders/RoleSeeder.php` - Seeder pour les rôles et permissions
- `database/seeders/UserSeeder.php` - Seeder pour les utilisateurs de test
- `routes/api.php` - Routes API
- `config/auth.php` - Configuration d'authentification
- `config/permission.php` - Configuration Spatie Permission
- Migrations pour Sanctum et Spatie Permission
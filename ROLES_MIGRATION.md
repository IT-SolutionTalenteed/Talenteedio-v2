# Migration vers un système de rôles simplifié

Ce document explique la migration de Spatie Permission vers un système de rôles simplifié.

## Changements apportés

### 1. Nouveau modèle User
- Suppression du trait `HasRoles` de Spatie
- Ajout d'un champ `role` directement dans la table users
- Nouvelles méthodes : `hasRole()`, `hasAnyRole()`, `roleModel()`

### 2. Nouveau modèle SimpleRole
- Modèle simple pour gérer les rôles
- Table `simple_roles` avec `name` et `display_name`

### 3. Middleware mis à jour
- `CheckRole` utilise maintenant `$user->hasRole($role)`
- Nouveau `RoleMiddleware` comme alternative

### 4. Suppression des permissions
- Plus de gestion des permissions
- Système basé uniquement sur les rôles

## Migration des données

### Étape 1 : Sauvegarder les données existantes
```bash
php artisan migrate:simple-roles
```

### Étape 2 : Exécuter les nouvelles migrations
```bash
php artisan migrate
```

### Étape 3 : Seeder les nouveaux rôles
```bash
php artisan db:seed --class=RoleSeeder
```

## Utilisation

### Dans les routes
```php
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Routes admin
});
```

### Dans les contrôleurs
```php
if ($user->hasRole('admin')) {
    // Logique admin
}

if ($user->hasAnyRole(['admin', 'entreprise'])) {
    // Logique pour admin ou entreprise
}
```

### Assignation de rôles
```php
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => bcrypt('password'),
    'role' => 'talent'
]);

// Ou modifier un utilisateur existant
$user->role = 'admin';
$user->save();
```

## Rôles disponibles
- `admin` : Administrateur
- `talent` : Talent
- `entreprise` : Entreprise

## Suppression de Spatie Permission

Une fois la migration terminée et testée, vous pouvez :

1. Supprimer le package :
```bash
composer remove spatie/laravel-permission
```

2. Supprimer le fichier de configuration :
```bash
rm config/permission.php
```

3. Nettoyer les imports dans vos fichiers
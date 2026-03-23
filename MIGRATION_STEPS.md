# Guide de migration étape par étape

## Étapes de migration de Spatie Permission vers le système simplifié

### 1. Sauvegarder les données existantes (IMPORTANT)
```bash
# Sauvegarder la base de données
php artisan db:backup # ou votre méthode de sauvegarde

# Migrer les rôles existants vers le nouveau système
php artisan migrate:simple-roles
```

### 2. Exécuter les nouvelles migrations
```bash
php artisan migrate
```

### 3. Seeder les nouveaux rôles
```bash
php artisan db:seed --class=RoleSeeder
```

### 4. Tester le nouveau système
```bash
php test_simple_roles.php
```

### 5. Mettre à jour vos routes (si nécessaire)
Remplacer dans vos fichiers de routes :
```php
// Ancien
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Routes
});

// Nouveau (identique, pas de changement nécessaire)
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Routes
});
```

### 6. Supprimer Spatie Permission (optionnel)
Une fois que tout fonctionne :
```bash
composer remove spatie/laravel-permission
rm config/permission.php
```

### 7. Nettoyer les imports
Supprimer les imports Spatie dans vos fichiers :
- `use Spatie\Permission\Traits\HasRoles;`
- `use Spatie\Permission\Models\Role;`
- `use Spatie\Permission\Models\Permission;`

## Vérifications post-migration

### Tester l'authentification
```bash
# Test de connexion
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'
```

### Tester les rôles
```bash
# Test d'accès avec rôle
curl -X GET http://localhost:8000/api/admin/users \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Vérifier les utilisateurs
```bash
php artisan tinker
>>> User::with('roleModel')->get()
```

## Rollback (si nécessaire)

Si vous devez revenir en arrière :

1. Restaurer la sauvegarde de base de données
2. Réinstaller Spatie Permission :
```bash
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```
3. Restaurer les anciens fichiers depuis votre système de contrôle de version
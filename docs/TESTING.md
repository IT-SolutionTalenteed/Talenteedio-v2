# Guide de test

## Scripts de test disponibles

### 1. test_simple_roles.php
Test du système de rôles simplifié
```bash
php test_simple_roles.php
```

**Tests effectués :**
- Création d'utilisateur avec rôle
- Méthode `hasRole()`
- Méthode `hasAnyRole()`
- Changement de rôle

### 2. test_media_categories.php
Test du système de catégories de média
```bash
php test_media_categories.php
```

**Tests effectués :**
- Création d'admin
- Création de catégorie
- Relations One-to-Many
- Scopes et filtres

### 3. test_foreign_keys.php
Test des contraintes de foreign keys
```bash
php test_foreign_keys.php
```

**Tests effectués :**
- Création avec FK valide
- Relations bidirectionnelles
- Tentative avec FK invalide (doit échouer)
- Test CASCADE DELETE

## Tests unitaires Laravel

### Lancer tous les tests
```bash
php artisan test
```

### Tests spécifiques
```bash
# Tests d'authentification
php artisan test --filter AuthTest

# Tests des rôles
php artisan test --filter RoleTest

# Tests des catégories
php artisan test --filter MediaCategoryTest
```

## Tests d'API avec curl

### Authentification
```bash
# Inscription
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password",
    "password_confirmation": "password",
    "role": "admin"
  }'

# Connexion
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password"
  }'
```

### Catégories de média (Admin)
```bash
# Créer une catégorie
curl -X POST http://localhost:8000/api/admin/media-categories \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Category",
    "description": "Description de test",
    "is_active": true
  }'

# Lister les catégories
curl -X GET http://localhost:8000/api/admin/media-categories \
  -H "Authorization: Bearer YOUR_TOKEN"

# Modifier une catégorie
curl -X PUT http://localhost:8000/api/admin/media-categories/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Updated Category",
    "description": "Description mise à jour"
  }'

# Supprimer une catégorie
curl -X DELETE http://localhost:8000/api/admin/media-categories/1 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Tests de permissions
```bash
# Accès admin (doit réussir)
curl -X GET http://localhost:8000/api/admin/dashboard \
  -H "Authorization: Bearer ADMIN_TOKEN"

# Accès talent à l'admin (doit échouer)
curl -X GET http://localhost:8000/api/admin/dashboard \
  -H "Authorization: Bearer TALENT_TOKEN"
```

## Tests de base de données

### Vérifier les contraintes FK
```sql
-- Tenter d'insérer avec FK invalide (doit échouer)
INSERT INTO media_categories (name, slug, created_by) 
VALUES ('Test', 'test', 99999);

-- Vérifier CASCADE DELETE
DELETE FROM users WHERE id = 1;
SELECT COUNT(*) FROM media_categories WHERE created_by = 1; -- Doit être 0
```

### Vérifier les index
```sql
SHOW INDEX FROM media_categories;
SHOW INDEX FROM users;
```

## Checklist de test avant déploiement

### ✅ Authentification
- [ ] Inscription avec validation
- [ ] Connexion/déconnexion
- [ ] Tokens Sanctum

### ✅ Rôles et permissions
- [ ] Middleware de rôles
- [ ] Accès admin uniquement
- [ ] Accès talent/entreprise

### ✅ Catégories de média
- [ ] CRUD complet (admin)
- [ ] Validation des données
- [ ] Relations FK
- [ ] CASCADE DELETE

### ✅ Base de données
- [ ] Migrations sans erreur
- [ ] Seeders fonctionnels
- [ ] Contraintes FK actives
- [ ] Index performants

### ✅ API
- [ ] Endpoints documentés
- [ ] Codes de statut corrects
- [ ] Validation des entrées
- [ ] Gestion des erreurs

## Outils recommandés

### Pour les tests API
- **Postman** : Collection d'endpoints
- **Insomnia** : Alternative à Postman
- **curl** : Tests en ligne de commande

### Pour les tests de performance
- **Laravel Telescope** : Monitoring des requêtes
- **Debugbar** : Profiling en développement

### Pour les tests automatisés
- **PHPUnit** : Tests unitaires Laravel
- **Pest** : Alternative moderne à PHPUnit
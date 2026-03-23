# Documentation du projet Talenteedio v2

## Structure de la documentation

Ce dossier contient toute la documentation technique du projet.

### Fichiers disponibles :

#### 📋 **API_SETUP.md**
Configuration et setup de l'API Laravel avec Sanctum

#### 🏗️ **CONTROLLERS_STRUCTURE.md**
Structure des contrôleurs organisés par rôle et système de catégories de média
- Organisation des dossiers Admin/Talent/Entreprise
- Système CRUD des catégories de média
- Relations One-to-Many (Admin → MediaCategory)
- Routes API et permissions

#### 🗄️ **DATABASE_SCHEMA.md**
Schéma complet de la base de données
- Tables et relations
- Foreign Keys et contraintes
- Index et performances
- Ordre des migrations

#### 🔗 **FOREIGN_KEYS.md**
Documentation détaillée des Foreign Keys
- Relations et contraintes
- Tests et validation
- Bonnes pratiques
- Dépannage

#### 🔄 **MIGRATION_STEPS.md**
Guide étape par étape pour migrer de Spatie Permission vers le système simplifié

#### 👥 **ROLES_MIGRATION.md**
Documentation complète sur la migration du système de rôles
- Changements apportés
- Nouveau modèle User
- Suppression des permissions
- Utilisation du nouveau système

#### 📖 **README.md**
Documentation générale du projet (fichier principal)

#### 🧪 **TESTING.md**
Guide complet des tests et validation
- Scripts de test disponibles
- Tests d'API avec curl
- Checklist avant déploiement

## Architecture du projet

### Système d'authentification
- Laravel Sanctum pour l'API
- Rôles simplifiés : admin, talent, entreprise
- Middleware de vérification des rôles

### Base de données
- **Users** : Utilisateurs avec rôle direct
- **SimpleRoles** : Table des rôles disponibles
- **MediaCategories** : Catégories de média créées par les admins

### Relations importantes
```
User (admin) ---> MediaCategory (One-to-Many)
User ---> SimpleRole (Many-to-One via role field)
```

### Foreign Keys
- `media_categories.created_by` → `users.id` (CASCADE)
- `users.role` → `simple_roles.name` (référence textuelle)

## Commandes utiles

```bash
# Migration complète
php artisan migrate:simple-roles
php artisan migrate
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=MediaCategorySeeder

# Tests
php test_simple_roles.php
php test_media_categories.php
```

## Structure des contrôleurs

```
app/Http/Controllers/
├── Admin/
│   ├── DashboardController.php
│   └── MediaCategoryController.php
├── Talent/
│   └── DashboardController.php
├── Entreprise/
│   └── DashboardController.php
├── AuthController.php
└── UserController.php
```

## Prochaines étapes

1. Implémenter le système de projets
2. Ajouter la gestion des médias
3. Créer le système de matching talent/entreprise
4. Développer les notifications
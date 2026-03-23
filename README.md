# Talenteedio v2

Plateforme de mise en relation entre talents créatifs et entreprises.

## 🚀 Démarrage rapide

### Prérequis
- PHP 8.2+
- Composer
- SQLite ou MySQL
- Node.js (pour les assets frontend)

### Installation

1. **Cloner le projet**
```bash
git clone <repository-url>
cd talenteedio-v2
```

2. **Installer les dépendances**
```bash
composer install
npm install
```

3. **Configuration**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Base de données**
```bash
php artisan migrate
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=MediaCategorySeeder
```

5. **Lancer le serveur**
```bash
php artisan serve
```

## 📚 Documentation

Toute la documentation technique se trouve dans le dossier [`docs/`](./docs/):

- **[INDEX.md](./docs/INDEX.md)** - Vue d'ensemble de la documentation
- **[CONTROLLERS_STRUCTURE.md](./docs/CONTROLLERS_STRUCTURE.md)** - Structure des contrôleurs et API
- **[ROLES_MIGRATION.md](./docs/ROLES_MIGRATION.md)** - Système de rôles simplifié
- **[MIGRATION_STEPS.md](./docs/MIGRATION_STEPS.md)** - Guide de migration
- **[API_SETUP.md](./docs/API_SETUP.md)** - Configuration de l'API

## 🏗️ Architecture

### Rôles utilisateurs
- **Admin** : Gestion complète de la plateforme
- **Talent** : Créateurs de contenu
- **Entreprise** : Clients cherchant des talents

### Fonctionnalités principales
- ✅ Authentification avec Laravel Sanctum
- ✅ Système de rôles simplifié
- ✅ Gestion des catégories de média (Admin)
- ✅ Tableaux de bord par rôle
- 🔄 Gestion des projets (en cours)
- 🔄 Système de matching (à venir)

### API Endpoints

#### Authentification
- `POST /api/register` - Inscription
- `POST /api/login` - Connexion
- `POST /api/logout` - Déconnexion

#### Admin
- `GET /api/admin/dashboard` - Tableau de bord
- `GET /api/admin/media-categories` - Liste des catégories
- `POST /api/admin/media-categories` - Créer une catégorie

#### Talent/Entreprise
- `GET /api/talent/dashboard` - Tableau de bord talent
- `GET /api/entreprise/dashboard` - Tableau de bord entreprise

## 🧪 Tests

```bash
# Tests unitaires
php artisan test

# Tests manuels
php test_simple_roles.php
php test_media_categories.php
```

## 🤝 Contribution

1. Fork le projet
2. Créer une branche feature (`git checkout -b feature/AmazingFeature`)
3. Commit les changes (`git commit -m 'Add some AmazingFeature'`)
4. Push vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrir une Pull Request

## 📝 License

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.
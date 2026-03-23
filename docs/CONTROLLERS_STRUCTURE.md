# Structure des contrôleurs par rôle

## Organisation des dossiers

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
├── Controller.php
└── UserController.php
```

## Système de catégories de média

### Modèle MediaCategory

**Champs :**
- `id` : Identifiant unique
- `name` : Nom de la catégorie
- `description` : Description (optionnelle)
- `slug` : Slug généré automatiquement
- `is_active` : Statut actif/inactif
- `created_by` : ID de l'admin créateur
- `created_at` / `updated_at` : Timestamps

**Relations :**
- `creator()` : Relation avec l'utilisateur qui a créé la catégorie
- Un admin peut créer plusieurs catégories (One-to-Many)

### Routes API

#### Routes Admin (`/api/admin/`)
- `GET /dashboard` : Tableau de bord avec statistiques
- `GET /media-categories` : Liste toutes les catégories
- `POST /media-categories` : Créer une nouvelle catégorie
- `GET /media-categories/{id}` : Voir une catégorie
- `PUT /media-categories/{id}` : Modifier une catégorie
- `DELETE /media-categories/{id}` : Supprimer une catégorie
- `PATCH /media-categories/{id}/toggle-status` : Activer/désactiver
- `GET /media-categories-active` : Liste des catégories actives

#### Routes Talent (`/api/talent/`)
- `GET /dashboard` : Tableau de bord talent
- `GET /media-categories` : Liste des catégories actives seulement

#### Routes Entreprise (`/api/entreprise/`)
- `GET /dashboard` : Tableau de bord entreprise
- `GET /media-categories` : Liste des catégories actives seulement

## Utilisation

### Créer une catégorie (Admin seulement)
```bash
curl -X POST http://localhost:8000/api/admin/media-categories \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Vidéo",
    "description": "Contenu vidéo incluant films, documentaires, clips",
    "is_active": true
  }'
```

### Lister les catégories actives (Tous les rôles)
```bash
curl -X GET http://localhost:8000/api/talent/media-categories \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Modifier une catégorie (Admin seulement)
```bash
curl -X PUT http://localhost:8000/api/admin/media-categories/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Vidéo HD",
    "description": "Contenu vidéo haute définition",
    "is_active": true
  }'
```

## Permissions

- **Admin** : CRUD complet sur les catégories de média
- **Talent** : Lecture seule des catégories actives
- **Entreprise** : Lecture seule des catégories actives

## Seeder

Le `MediaCategorySeeder` crée automatiquement :
- Un admin par défaut (`admin@talenteedio.com`)
- 5 catégories de base : Vidéo, Audio, Image, Document, Animation

Pour exécuter le seeder :
```bash
php artisan db:seed --class=MediaCategorySeeder
```

## Validation

Les requests `StoreMediaCategoryRequest` et `UpdateMediaCategoryRequest` gèrent :
- Validation des champs
- Autorisation (admin seulement)
- Messages d'erreur en français
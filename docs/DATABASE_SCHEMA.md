# Schéma de base de données

## Tables et relations

### 1. Users
```sql
CREATE TABLE users (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(255) DEFAULT 'talent',
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

### 2. Simple Roles
```sql
CREATE TABLE simple_roles (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) UNIQUE NOT NULL,
    display_name VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

### 3. Media Categories
```sql
CREATE TABLE media_categories (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_by BIGINT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    -- Foreign Key Constraints
    CONSTRAINT fk_media_categories_created_by 
        FOREIGN KEY (created_by) 
        REFERENCES users(id) 
        ON DELETE CASCADE
);
```

## Relations et Foreign Keys

### MediaCategory → User (created_by)
- **Type** : Many-to-One (Plusieurs catégories peuvent être créées par un admin)
- **Foreign Key** : `media_categories.created_by → users.id`
- **Contrainte** : `ON DELETE CASCADE`
- **Signification** : Si un admin est supprimé, toutes ses catégories sont supprimées

### User → SimpleRole (role)
- **Type** : Many-to-One (Plusieurs utilisateurs peuvent avoir le même rôle)
- **Référence** : `users.role → simple_roles.name` (référence textuelle)
- **Pas de contrainte FK** : Utilise une validation au niveau application

## Index et performances

### Index automatiques
- `users.email` (UNIQUE)
- `simple_roles.name` (UNIQUE)
- `media_categories.slug` (UNIQUE)
- `media_categories.created_by` (FK automatique)

### Index recommandés
```sql
-- Pour les requêtes par rôle
CREATE INDEX idx_users_role ON users(role);

-- Pour les catégories actives
CREATE INDEX idx_media_categories_active ON media_categories(is_active);

-- Pour les requêtes par créateur
CREATE INDEX idx_media_categories_created_by ON media_categories(created_by);
```

## Contraintes de validation

### Au niveau base de données
- `users.email` : UNIQUE
- `simple_roles.name` : UNIQUE
- `media_categories.slug` : UNIQUE
- `media_categories.created_by` : NOT NULL + FK

### Au niveau application (Laravel)
- `users.role` : IN ('admin', 'talent', 'entreprise')
- `media_categories.name` : UNIQUE (validation Laravel)
- `media_categories.slug` : Généré automatiquement

## Migrations importantes

### Ordre d'exécution
1. `create_users_table` (table de base)
2. `create_simple_roles_table`
3. `add_role_to_users_table`
4. `create_media_categories_table` (dépend de users)
5. `drop_spatie_permission_tables` (nettoyage)

### Commandes de migration
```bash
# Migration complète
php artisan migrate

# Rollback si nécessaire
php artisan migrate:rollback --step=1
```

## Seeders

### RoleSeeder
Crée les rôles de base :
- admin (Administrateur)
- talent (Talent)
- entreprise (Entreprise)

### MediaCategorySeeder
Crée :
- Un admin par défaut
- 5 catégories de base (Vidéo, Audio, Image, Document, Animation)

## Vérification des contraintes

### Test des foreign keys
```sql
-- Cette requête doit échouer (violation FK)
INSERT INTO media_categories (name, slug, created_by) 
VALUES ('Test', 'test', 999);

-- Cette requête doit réussir
INSERT INTO media_categories (name, slug, created_by) 
VALUES ('Test', 'test', 1);
```

### Test CASCADE
```sql
-- Supprimer un admin doit supprimer ses catégories
DELETE FROM users WHERE id = 1;
-- Les catégories avec created_by = 1 sont automatiquement supprimées
```
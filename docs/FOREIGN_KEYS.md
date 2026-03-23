# Foreign Keys et Relations

## Vue d'ensemble des Foreign Keys

### 1. MediaCategory → User (created_by)

**Définition dans la migration :**
```php
$table->foreignId('created_by')->constrained('users')->onDelete('cascade');
```

**Détails :**
- **Table source** : `media_categories`
- **Colonne** : `created_by`
- **Table cible** : `users`
- **Colonne cible** : `id`
- **Action CASCADE** : `ON DELETE CASCADE`
- **Type de relation** : Many-to-One (Plusieurs catégories → Un admin)

**Signification :**
- Chaque catégorie de média doit être créée par un utilisateur existant
- Si l'utilisateur créateur est supprimé, toutes ses catégories sont automatiquement supprimées
- Garantit l'intégrité référentielle

### 2. User → SimpleRole (référence textuelle)

**Définition :**
```php
// Dans la migration users
$table->string('role')->default('talent');

// Pas de FK physique, validation au niveau application
```

**Détails :**
- **Table source** : `users`
- **Colonne** : `role`
- **Table cible** : `simple_roles`
- **Colonne cible** : `name`
- **Type** : Référence textuelle (pas de FK physique)
- **Validation** : Au niveau application Laravel

## Relations Eloquent

### Dans le modèle MediaCategory
```php
/**
 * Relation avec l'utilisateur qui a créé la catégorie
 */
public function creator(): BelongsTo
{
    return $this->belongsTo(User::class, 'created_by');
}
```

### Dans le modèle User
```php
/**
 * Relation avec les catégories de média créées par cet utilisateur
 */
public function mediaCategories()
{
    return $this->hasMany(MediaCategory::class, 'created_by');
}

/**
 * Get the role model
 */
public function roleModel()
{
    return $this->belongsTo(SimpleRole::class, 'role', 'name');
}
```

## Contraintes et validations

### Au niveau base de données
```sql
-- Contrainte FK automatique
ALTER TABLE media_categories 
ADD CONSTRAINT fk_media_categories_created_by 
FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE;
```

### Au niveau application (Laravel)
```php
// Validation dans StoreMediaCategoryRequest
'created_by' => 'required|exists:users,id'

// Validation du rôle dans AuthController
'role' => 'required|string|in:admin,talent,entreprise'
```

## Tests des Foreign Keys

### Test de création valide
```php
$admin = User::create(['name' => 'Admin', 'email' => 'admin@test.com', 'role' => 'admin']);
$category = MediaCategory::create([
    'name' => 'Test Category',
    'created_by' => $admin->id // FK valide
]);
```

### Test de violation FK (doit échouer)
```php
try {
    MediaCategory::create([
        'name' => 'Invalid Category',
        'created_by' => 99999 // ID inexistant
    ]);
} catch (QueryException $e) {
    // Violation de contrainte FK
}
```

### Test CASCADE DELETE
```php
$admin = User::create(['name' => 'Admin', 'email' => 'admin@test.com']);
$category = MediaCategory::create(['name' => 'Test', 'created_by' => $admin->id]);

$admin->delete(); // Supprime l'admin
// La catégorie est automatiquement supprimée (CASCADE)
```

## Avantages des Foreign Keys

### 1. Intégrité référentielle
- Empêche les données orphelines
- Garantit la cohérence des relations
- Validation automatique au niveau DB

### 2. Performance
- Index automatique sur les colonnes FK
- Optimisation des jointures
- Requêtes plus rapides

### 3. Maintenance
- Suppression en cascade automatique
- Moins de code de nettoyage manuel
- Réduction des bugs liés aux données

## Bonnes pratiques

### 1. Nommage des FK
```php
// ✅ Bon : explicite et clair
$table->foreignId('created_by')->constrained('users');

// ❌ Éviter : ambigu
$table->foreignId('user_id')->constrained();
```

### 2. Actions CASCADE
```php
// ✅ Pour les relations de propriété
->onDelete('cascade')

// ✅ Pour les relations de référence
->onDelete('set null')

// ❌ Éviter : peut causer des suppressions non voulues
->onDelete('cascade') // sur toutes les relations
```

### 3. Index
```php
// ✅ Index automatique avec foreignId()
$table->foreignId('created_by')->constrained('users');

// ✅ Index manuel si nécessaire
$table->index(['created_by', 'is_active']);
```

## Dépannage

### Erreur de contrainte FK
```
SQLSTATE[23000]: Integrity constraint violation: 1452 Cannot add or update a child row
```
**Solution :** Vérifier que la valeur de la FK existe dans la table parent

### Erreur CASCADE DELETE
```
SQLSTATE[23000]: Integrity constraint violation: 1451 Cannot delete or update a parent row
```
**Solution :** Définir l'action CASCADE ou supprimer manuellement les enregistrements enfants

### Performance lente
**Solution :** Vérifier les index sur les colonnes FK
```sql
SHOW INDEX FROM media_categories WHERE Column_name = 'created_by';
```
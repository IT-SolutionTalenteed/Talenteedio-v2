# Debug Upload d'Images - Événements

## Problème rencontré

Erreur 422 lors de l'upload d'image pour un événement :
```json
{
  "message": "validation.uploaded",
  "errors": {
    "image_mise_en_avant": ["validation.uploaded"]
  }
}
```

## Causes possibles

### 1. Taille du fichier trop grande

**Vérifier :**
```bash
# Voir la configuration PHP actuelle
php -i | grep upload_max_filesize
php -i | grep post_max_size
```

**Solution :**
Le fichier `php-dev.ini` est déjà configuré avec :
- `upload_max_filesize = 500M`
- `post_max_size = 900M`

S'assurer que le serveur utilise cette configuration :
```bash
php -c php-dev.ini artisan serve
```

### 2. Permissions sur le dossier storage

**Vérifier :**
```bash
ls -la storage/app/public/evenements
```

**Solution :**
```bash
chmod -R 775 storage/app/public
chown -R $USER:www-data storage/app/public
```

### 3. Fichier corrompu ou invalide

**Vérifier :**
- Le fichier est-il une image valide ?
- Le format est-il supporté ? (jpg, jpeg, png, gif, webp)
- La taille est-elle < 5MB (5120KB) ?

### 4. Problème avec FormData

**Vérifier dans le navigateur (Console) :**
```javascript
// Avant l'envoi, vérifier le FormData
const fd = buildFormData()
for (let pair of fd.entries()) {
  console.log(pair[0], pair[1])
}
```

## Corrections appliquées

### 1. Ajout de logs dans le contrôleur

Le contrôleur log maintenant les informations sur le fichier :
```php
\Log::info('Update evenement request', [
    'has_file' => $request->hasFile('image_mise_en_avant'),
    'file_valid' => $request->file('image_mise_en_avant')->isValid(),
    'file_size' => $request->file('image_mise_en_avant')->getSize(),
    'file_error' => $request->file('image_mise_en_avant')->getError(),
]);
```

**Consulter les logs :**
```bash
tail -f storage/logs/laravel.log
```

### 2. Vérification de validité du fichier

Le contrôleur vérifie maintenant que le fichier est valide avant de le traiter :
```php
if ($request->hasFile('image_mise_en_avant') && $request->file('image_mise_en_avant')->isValid()) {
    // Traitement du fichier
}
```

### 3. N'envoyer l'image que si nécessaire

Le frontend n'envoie l'image que si un nouveau fichier a été sélectionné :
```javascript
if (imageFile.value && imageFile.value instanceof File) {
  fd.append('image_mise_en_avant', imageFile.value)
}
```

## Tests à effectuer

### 1. Tester avec une petite image

```bash
# Créer une image de test de 100KB
convert -size 100x100 xc:blue test.jpg
```

### 2. Vérifier les logs

```bash
# Terminal 1 : Suivre les logs
tail -f storage/logs/laravel.log

# Terminal 2 : Faire l'upload
# Puis vérifier les logs dans le terminal 1
```

### 3. Tester sans image

Essayer de modifier un événement sans changer l'image pour vérifier que ça fonctionne.

### 4. Tester la création (POST)

Vérifier si le problème existe aussi lors de la création d'un nouvel événement.

## Codes d'erreur PHP pour les uploads

| Code | Constante | Description |
|------|-----------|-------------|
| 0 | UPLOAD_ERR_OK | Pas d'erreur |
| 1 | UPLOAD_ERR_INI_SIZE | Fichier > upload_max_filesize |
| 2 | UPLOAD_ERR_FORM_SIZE | Fichier > MAX_FILE_SIZE du formulaire |
| 3 | UPLOAD_ERR_PARTIAL | Fichier partiellement uploadé |
| 4 | UPLOAD_ERR_NO_FILE | Aucun fichier uploadé |
| 6 | UPLOAD_ERR_NO_TMP_DIR | Dossier temporaire manquant |
| 7 | UPLOAD_ERR_CANT_WRITE | Échec d'écriture sur le disque |
| 8 | UPLOAD_ERR_EXTENSION | Extension PHP a arrêté l'upload |

## Commandes utiles

### Vérifier la configuration PHP
```bash
php -i | grep -E "(upload_max_filesize|post_max_size|max_execution_time)"
```

### Vérifier les permissions
```bash
ls -la storage/app/public/
```

### Nettoyer le cache
```bash
php artisan cache:clear
php artisan config:clear
```

### Recréer le lien symbolique
```bash
php artisan storage:link
```

### Tester l'upload manuellement
```bash
# Créer un script de test
cat > test_upload.php << 'EOF'
<?php
$target_dir = "storage/app/public/evenements/";
$target_file = $target_dir . basename($_FILES["image"]["name"]);

if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
    echo "OK";
} else {
    echo "ERROR: " . $_FILES["image"]["error"];
}
EOF

# Tester avec curl
curl -F "image=@test.jpg" http://localhost:8000/test_upload.php
```

## Solution temporaire

Si le problème persiste, utiliser une approche en deux étapes :

1. Créer/modifier l'événement sans l'image
2. Uploader l'image séparément via un endpoint dédié

### Créer un endpoint dédié

```php
// Dans EvenementController.php
public function uploadImage(Request $request, Evenement $evenement)
{
    $request->validate([
        'image' => 'required|image|mimes:jpg,jpeg,png,gif,webp|max:5120'
    ]);

    if ($evenement->image_mise_en_avant) {
        Storage::disk('public')->delete($evenement->image_mise_en_avant);
    }

    $path = $request->file('image')->store('evenements', 'public');
    $evenement->update(['image_mise_en_avant' => $path]);

    return response()->json($evenement);
}
```

### Route
```php
Route::post('/admin/evenements/{evenement}/upload-image', [EvenementController::class, 'uploadImage']);
```

## Prochaines étapes

1. ✅ Ajouter les logs pour identifier le problème exact
2. ✅ Vérifier que le fichier est valide avant traitement
3. ✅ N'envoyer l'image que si nécessaire
4. ⏳ Tester avec différentes tailles d'images
5. ⏳ Vérifier les logs Laravel
6. ⏳ Implémenter l'upload en deux étapes si nécessaire

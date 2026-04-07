# Fix Upload d'Images en Production

## Problème

Erreur 422 lors de l'upload d'images sur le serveur de production (talenteed.io) :
```json
{
  "message": "validation.uploaded",
  "errors": {
    "image_mise_en_avant": ["validation.uploaded"]
  }
}
```

## Cause

La configuration PHP du serveur de production ne permet pas l'upload de fichiers ou les limites sont trop basses.

## Solution

### Étape 1 : Vérifier la configuration actuelle

Accéder à l'URL de diagnostic :
```
https://talenteed.io/api/diagnostic/config
```

Cela affichera :
- Configuration PHP (upload_max_filesize, post_max_size, etc.)
- Permissions des dossiers
- Recommandations

### Étape 2 : Se connecter au serveur

```bash
ssh user@talenteed.io
cd /path/to/Talenteedio-v2
```

### Étape 3 : Vérifier les permissions

```bash
# Vérifier les permissions actuelles
ls -la storage/app/public/

# Corriger si nécessaire
chmod -R 775 storage/app/public
chown -R www-data:www-data storage/app/public

# Créer le dossier evenements s'il n'existe pas
mkdir -p storage/app/public/evenements
chmod 775 storage/app/public/evenements
```

### Étape 4 : Vérifier la configuration PHP

```bash
# Exécuter le script de diagnostic
php scripts/check_php_config.php
```

### Étape 5 : Configurer PHP

#### Option A : Via .user.ini (recommandé pour hébergement partagé)

Le fichier `public/.user.ini` a déjà été créé avec :
```ini
upload_max_filesize = 10M
post_max_size = 20M
max_execution_time = 300
max_input_time = 300
memory_limit = 256M
```

**Important :** Redémarrer PHP-FPM pour que les changements prennent effet :
```bash
# Sur Ubuntu/Debian
sudo systemctl restart php8.2-fpm

# Ou selon votre version PHP
sudo systemctl restart php-fpm
```

#### Option B : Via php.ini (si vous avez accès)

Éditer le fichier php.ini :
```bash
# Trouver le fichier php.ini
php --ini

# Éditer
sudo nano /etc/php/8.2/fpm/php.ini
```

Modifier les valeurs :
```ini
upload_max_filesize = 10M
post_max_size = 20M
max_execution_time = 300
max_input_time = 300
memory_limit = 256M
```

Redémarrer PHP-FPM :
```bash
sudo systemctl restart php8.2-fpm
```

#### Option C : Via .htaccess (si mod_php est activé)

Le fichier `public/.htaccess` a déjà été mis à jour avec :
```apache
<IfModule mod_php.c>
    php_value upload_max_filesize 10M
    php_value post_max_size 20M
    php_value max_execution_time 300
    php_value max_input_time 300
    php_value memory_limit 256M
</IfModule>
```

### Étape 6 : Vérifier Nginx (si applicable)

Si vous utilisez Nginx, vérifier la configuration :

```bash
sudo nano /etc/nginx/sites-available/talenteed.io
```

Ajouter ou vérifier :
```nginx
server {
    # ...
    
    # Augmenter la taille max des uploads
    client_max_body_size 20M;
    
    # Timeouts
    client_body_timeout 300s;
    client_header_timeout 300s;
    
    # ...
}
```

Redémarrer Nginx :
```bash
sudo nginx -t
sudo systemctl restart nginx
```

### Étape 7 : Tester l'upload

#### Via l'API de diagnostic

```bash
# Créer un fichier de test
echo "Test" > test.txt

# Tester l'upload
curl -X POST https://talenteed.io/api/diagnostic/test-upload \
  -F "file=@test.txt" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### Via l'interface admin

1. Se connecter à l'admin
2. Aller dans Événements
3. Modifier un événement
4. Uploader une petite image (< 1MB)
5. Vérifier que ça fonctionne

### Étape 8 : Déployer les changements

```bash
# Sur le serveur
cd /path/to/Talenteedio-v2

# Pull les derniers changements
git pull origin main

# Vérifier que les fichiers sont présents
ls -la public/.user.ini
ls -la public/.htaccess

# Redémarrer les services
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx  # si applicable
```

## Vérifications post-déploiement

### 1. Vérifier la configuration

```bash
# Via l'API
curl https://talenteed.io/api/diagnostic/config

# Ou via le navigateur
https://talenteed.io/api/diagnostic/config
```

Vérifier que :
- `upload_max_filesize` >= 10M
- `post_max_size` >= 20M
- `post_max_size` > `upload_max_filesize`
- Tous les dossiers sont accessibles en écriture

### 2. Tester l'upload

Essayer d'uploader une image via l'interface admin.

### 3. Consulter les logs

```bash
# Logs Laravel
tail -f storage/logs/laravel.log

# Logs PHP-FPM
sudo tail -f /var/log/php8.2-fpm.log

# Logs Nginx
sudo tail -f /var/log/nginx/error.log
```

## Problèmes courants

### Le fichier .user.ini n'est pas pris en compte

**Cause :** PHP-FPM n'a pas été redémarré ou le cache n'a pas été vidé.

**Solution :**
```bash
sudo systemctl restart php8.2-fpm
# Attendre 5 minutes (cache .user.ini)
```

### Erreur "Permission denied"

**Cause :** Permissions incorrectes sur storage/

**Solution :**
```bash
chmod -R 775 storage
chown -R www-data:www-data storage
```

### Erreur "No space left on device"

**Cause :** Disque plein

**Solution :**
```bash
# Vérifier l'espace disque
df -h

# Nettoyer les logs
sudo journalctl --vacuum-time=7d

# Nettoyer les fichiers temporaires
sudo rm -rf /tmp/*
```

### L'upload fonctionne en local mais pas en production

**Cause :** Configuration PHP différente

**Solution :**
1. Comparer les configurations : `php -i | grep upload`
2. Vérifier les logs : `tail -f storage/logs/laravel.log`
3. Utiliser l'API de diagnostic

## Sécurité

⚠️ **Important :** Protéger les routes de diagnostic en production !

Ajouter une authentification :

```php
// routes/api.php
Route::prefix('diagnostic')->middleware('auth:sanctum')->group(function () {
    Route::get('/config', [DiagnosticController::class, 'checkConfig']);
    Route::post('/test-upload', [DiagnosticController::class, 'testUpload']);
});
```

Ou désactiver complètement en production :

```php
// routes/api.php
if (config('app.debug')) {
    Route::prefix('diagnostic')->group(function () {
        Route::get('/config', [DiagnosticController::class, 'checkConfig']);
        Route::post('/test-upload', [DiagnosticController::class, 'testUpload']);
    });
}
```

## Checklist de déploiement

- [ ] Fichiers déployés (`.user.ini`, `.htaccess`)
- [ ] Permissions correctes sur `storage/`
- [ ] PHP-FPM redémarré
- [ ] Nginx redémarré (si applicable)
- [ ] Configuration vérifiée via `/api/diagnostic/config`
- [ ] Test d'upload réussi
- [ ] Routes de diagnostic protégées ou désactivées
- [ ] Logs vérifiés

## Support

Si le problème persiste après avoir suivi toutes ces étapes :

1. Consulter les logs : `storage/logs/laravel.log`
2. Vérifier la configuration : `https://talenteed.io/api/diagnostic/config`
3. Contacter l'hébergeur pour vérifier les limites PHP

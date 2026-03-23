# Seeders Documentation

## AdminUserSeeder

Crée un utilisateur administrateur par défaut pour l'application.

### Informations de connexion
- **Email**: solofonirina35@gmail.com
- **Mot de passe**: STDlux06@
- **Rôle**: admin

### Utilisation

Exécuter uniquement ce seeder :
```bash
php artisan db:seed --class=AdminUserSeeder
```

Exécuter tous les seeders (inclut AdminUserSeeder) :
```bash
php artisan db:seed
```

### Sécurité
- Le mot de passe est hashé avec bcrypt
- L'email est vérifié automatiquement
- Utilise `firstOrCreate` pour éviter les doublons

### Note
Cet utilisateur admin peut se connecter immédiatement sur le frontend Vue.js et sera redirigé vers `/admin`.
# Talenteed v2 — API Laravel

Plateforme de mise en relation entre talents et entreprises via événements de recrutement.

## Prérequis

- PHP 8.4+
- Composer
- MySQL
- XAMPP (ou équivalent) pour MySQL
- MailDev (mails en développement)

## Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
```

## Configuration `.env`

```env
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=talenteed
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_FROM_ADDRESS=no-reply@talenteed.com
MAIL_FROM_NAME=Talenteed

OPENAI_API_KEY=sk-...
```

## Base de données

```bash
php artisan migrate
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=AdminSeeder
```

## Lancer l'API

> Le serveur doit être lancé avec les limites PHP étendues pour permettre l'upload de fichiers (images, vidéos).

```bash
php -c php-dev.ini artisan serve
```

Ou via le script fourni :

```bash
./serve.sh
```

L'API est disponible sur `http://localhost:8000`.

## Mails automatiques (scheduler)

Les rappels d'entretien (M-05) et demandes de feedback (M-06) sont envoyés via des commandes planifiées.
Lancer le scheduler dans un terminal séparé :

```bash
php artisan schedule:work
```

Pour tester une commande manuellement :

```bash
php artisan entretien:rappel
php artisan entretien:demander-feedback
```

## MailDev (visualiser les mails en dev)

```bash
maildev
```

Interface disponible sur `http://localhost:1080`.

## Storage (upload de fichiers)

Le symlink doit être créé une seule fois :

```bash
php artisan storage:link
```

Les fichiers uploadés sont accessibles via `/storage/...`.

## Config OpenAI

Publier le fichier de configuration si absent :

```bash
php artisan vendor:publish --provider="OpenAI\Laravel\ServiceProvider"
```

Puis renseigner `OPENAI_API_KEY` dans `.env`.

## Commandes utiles

```bash
# Vider les caches après modification du .env
php artisan config:clear
php artisan cache:clear

# Relancer toutes les migrations (repart de zéro)
php artisan migrate:fresh --seed

# Lister toutes les routes API
php artisan route:list --path=api
```

---

## Configuration Google OAuth (Connexion avec Google)

> La connexion Google est déjà implémentée côté code. Il faut juste configurer les credentials Google Cloud en production.

### 1. Créer un projet Google Cloud

1. Aller sur [console.cloud.google.com](https://console.cloud.google.com)
2. Créer un nouveau projet (ex : `Talenteed`)
3. Aller dans **API & Services > Identifiants**
4. Cliquer **Créer des identifiants > ID client OAuth 2.0**
5. Type d'application : **Application Web**
6. Nom : `Talenteed`

### 2. Configurer les URIs autorisées

Dans la fiche du client OAuth, renseigner :

**Origines JavaScript autorisées :**
```
https://votre-domaine.com
```

**URI de redirection autorisés :**
```
https://votre-domaine.com/api/auth/google/callback
```

> En développement local, remplacer par :
> - Origine : `http://localhost:8000` et `http://localhost:5173`
> - Redirect : `http://localhost:8000/api/auth/google/callback`

### 3. Renseigner dans `.env` (backend)

```env
GOOGLE_CLIENT_ID=xxxxxxxxx.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=GOCSPX-xxxxxxxxx
GOOGLE_REDIRECT_URI=https://votre-domaine.com/api/auth/google/callback
FRONTEND_URL=https://votre-domaine.com
```

### 4. Renseigner dans `.env` (frontend)

```env
VITE_API_URL=https://votre-domaine.com/api
```

### 5. Publier l'app Google (obligatoire en prod)

Par défaut, l'app OAuth est en mode **Test** (seulement les adresses ajoutées manuellement peuvent se connecter).

Pour la production :
1. Aller dans **API & Services > Écran de consentement OAuth**
2. Cliquer **Publier l'application**
3. Si l'app demande des scopes sensibles → vérification Google requise (quelques jours)
4. Pour les scopes `email` + `profile` uniquement → publication immédiate sans vérification

---

## Configuration reCAPTCHA (Login)

> Le widget reCAPTCHA v2 est déjà en place côté code.

### 1. Créer une clé reCAPTCHA

1. Aller sur [google.com/recaptcha/admin](https://www.google.com/recaptcha/admin)
2. **Type** : reCAPTCHA v2 — "Je ne suis pas un robot"
3. **Domaines** : ajouter `votre-domaine.com` (et `localhost` pour le dev)
4. Récupérer la **Clé du site** (frontend) et la **Clé secrète** (backend)

### 2. Renseigner les clés

**Backend `.env` :**
```env
RECAPTCHA_SECRET_KEY=6Lxxxxx...
```

**Frontend `.env` :**
```env
VITE_RECAPTCHA_SITE_KEY=6Lxxxxx...
```

---

## Stack

| Couche | Technologie |
|---|---|
| Framework | Laravel 13 |
| Auth | Laravel Sanctum |
| Rôles | SimpleRole (admin, talent, entreprise) |
| Base de données | MySQL |
| Mailing (dev) | MailDev (SMTP 1025 / UI 1080) |
| IA | OpenAI API (gpt-4o-mini) |
| Upload | disk `public` via `Storage::disk('public')` |

# Design — Boutons Hero + Africa Talent Observatory

*Date : 2026-07-14*
*Statut : validé, prêt pour le plan d'implémentation*

## Contexte & objectif

Sur la page d'accueil (`/home`), la section hero contient aujourd'hui un seul bouton
« Souscrire » qui pointe vers `router-link` `/profile-selection`.

Deux évolutions sont demandées :

1. Le bouton « Souscrire » doit rediriger vers le lien externe de billetterie
   `https://africatalentsummit.eventbrite.com` (nouvel onglet).
2. Ajouter un second bouton à côté, « Rejoindre Africa Talent Observatory »,
   qui mène à une nouvelle page de formulaire.

Le formulaire reproduit **la structure fonctionnelle** de la page
`https://www.koralytics.be/fr/collect` (mêmes champs / mêmes sections) — sans copier
le HTML/design propriétaire de ce site — avec notre propre titre, description et le
style visuel de Talenteed. Les données collectées sont enregistrées en base et
consultables dans le back office admin sous forme de tableau (lecture seule).

## Périmètre

- **Frontend** : dépôt `Front/` (Vue 3 + Vue Router + i18n).
- **Backend** : dépôt `Talenteedio-v2/` (Laravel + Sanctum).
- Aucune modification du projet `Talenteed-flutter/`.

## Contenu du formulaire

- **Titre** : `Africa Talent Observatory`
- **Description** : `cartographie des compétences des diasporas africaines en Europe, Africa Diaspora Skills Index`

Champs collectés (identiques à koralytics/collect) :

| Champ | Type | Requis | Notes |
|-------|------|--------|-------|
| Prénom | texte | oui | |
| Email | email | oui | validation email |
| Pays de résidence | select | oui | liste de pays codée dans le composant |
| Pays d'origine ou d'attachement | select | oui | liste de pays codée dans le composant |
| Secteur d'activité | select | oui | 12 options (Technologie, Ingénierie, Finance, Santé, Éducation, Agriculture, Transport, Médias, Arts, Hôtellerie, … Autre) |
| Lien avec la diaspora | select | oui | Membre / Association / Institution / Partenaire / Autre |
| Consentement traitement des données | case à cocher | **oui (obligatoire)** | doit être accepté pour envoyer |
| Consentement communications | case à cocher | non (optionnel) | |

Les listes d'options (pays, secteurs, liens) sont **codées en dur dans le composant Vue**.
Pas de nouvelles tables référentielles (YAGNI).

## Architecture

### Backend (`Talenteedio-v2/`)

1. **Migration** `create_observatory_submissions_table`
   - Colonnes : `id`, `prenom` (string), `email` (string), `pays_residence` (string),
     `pays_origine` (string), `secteur_activite` (string), `lien_diaspora` (string),
     `consent_data` (boolean), `consent_communications` (boolean, default false), `timestamps`.

2. **Modèle** `App\Models\ObservatorySubmission`
   - `$fillable` = toutes les colonnes ci-dessus.
   - casts `consent_data` / `consent_communications` en `boolean`.

3. **Contrôleur public** `App\Http\Controllers\Public\ObservatoryController@store`
   - Route : `POST /api/public/observatory` (dans le groupe `Route::prefix('public')`).
   - Validation :
     - `prenom` : required|string|max:255
     - `email` : required|email|max:255
     - `pays_residence` : required|string|max:255
     - `pays_origine` : required|string|max:255
     - `secteur_activite` : required|string|max:255
     - `lien_diaspora` : required|string|max:255
     - `consent_data` : accepted (obligatoire)
     - `consent_communications` : boolean (optionnel)
   - Crée la soumission et renvoie `201` avec un message de confirmation.

4. **Contrôleur admin** `App\Http\Controllers\Admin\ObservatorySubmissionController@index`
   - Route : `GET /api/admin/observatory-submissions` (groupe `role:admin`).
   - Lecture seule : renvoie toutes les soumissions triées par `created_at` décroissant.

### Frontend (`Front/`)

1. **Home.vue** (hero)
   - Bouton « Souscrire » → `<a href="https://africatalentsummit.eventbrite.com" target="_blank" rel="noopener" class="btn-primary">` (remplace le `router-link` vers `/profile-selection`).
   - Nouveau bouton `<router-link to="/africa-talent-observatory" class="btn-secondary">` libellé « Rejoindre Africa Talent Observatory ».
   - Clés i18n : `home.hero.subscribe` (existe déjà) + nouvelle clé `home.hero.joinObservatory`.

2. **Nouvelle page** `components/AfricaTalentObservatory.vue`
   - Route publique `/africa-talent-observatory` (name `AfricaTalentObservatory`).
   - Structure : `PublicNav` en tête, `Footer` en pied, conteneur central avec titre,
     description, formulaire (champs ci-dessus), bouton d'envoi.
   - Gestion des erreurs de validation par champ + message de succès après envoi.
   - Appelle le service pour POST vers l'API publique.

3. **Service** `services/observatoryService.js`
   - `submit(payload)` → `POST /public/observatory`.
   - `listAdmin()` → `GET /admin/observatory-submissions`.

4. **Back office** (tableau lecture seule)
   - Nouveau composant `components/admin/ObservatorySubmissionList.vue`.
     Tableau colonnes : Prénom, Email, Pays résidence, Pays origine, Secteur,
     Lien diaspora, Consentement données, Consentement comm., Date.
   - `AdminDashboard.vue` : import du composant + `v-if="activeTab === 'observatory-submissions'"`.
   - Routeur `router/index.js` : route enfant `{ path: 'observatory-submissions', name: 'AdminObservatorySubmissions', component: AdminDashboard, meta: adminMeta }`.
   - `components/layout/VerticalLayout.vue` : entrée de menu vers `AdminObservatorySubmissions`
     (regroupée avec les événements, ou une nouvelle rubrique selon cohérence).
   - `components/layout/Aside.vue` : entrée correspondante.

## Flux de données

```
Formulaire public (AfricaTalentObservatory.vue)
      │  POST /api/public/observatory
      ▼
ObservatoryController@store  →  table observatory_submissions
      ▲
      │  GET /api/admin/observatory-submissions
Back office (ObservatorySubmissionList.vue)  ← tableau lecture seule
```

## Gestion des erreurs

- **Backend** : validation Laravel renvoie `422` avec les erreurs par champ ;
  `consent_data` non coché ⇒ erreur de validation (empêche l'enregistrement).
- **Frontend** : affichage des erreurs `422` sous chaque champ ; message de succès
  après `201` ; désactivation du bouton pendant l'envoi.

## Tests / vérification

- Test de fumée manuel : soumettre le formulaire → vérifier l'apparition de la ligne
  dans le back office.
- Vérifier que le bouton Souscrire ouvre bien Eventbrite dans un nouvel onglet.
- (Optionnel) Feature test Laravel sur `POST /api/public/observatory`
  (cas succès + cas consentement manquant).

## Décisions & hypothèses

- Nommage anglais côté backend (`ObservatorySubmission`, `observatory_submissions`)
  cohérent avec les entités existantes (JobContract, MediaCategory…).
- Formulaire en français uniquement pour cette V1 (pas d'i18n complet du formulaire) ;
  le bouton hero passe par i18n car le hero est déjà internationalisé.
- Back office en **lecture seule** (pas de suppression ni export CSV pour cette V1).
- Doc de design placé côté backend ; commit uniquement sur demande explicite.

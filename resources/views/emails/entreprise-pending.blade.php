@extends('emails.layout')

@section('title', 'Demande en cours de vérification')
@section('header_badge', 'Compte recruteur')

@section('hero')
  <div style="text-align:center;">
    <div class="email-hero-icon" style="margin:0 auto 14px; background:rgba(242,159,31,0.2); border-color:rgba(242,159,31,0.4);">
      <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#f29f1f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
      </svg>
    </div>
    <div class="email-hero-title">Votre demande est en cours de vérification</div>
    <div class="email-hero-subtitle">Notre équipe examine votre dossier et vous contactera très prochainement.</div>
  </div>
@endsection

@section('content')
  <p class="email-greeting">Bonjour {{ $user->name }},</p>

  <p class="email-text">
    Merci d'avoir soumis votre demande d'inscription sur <strong style="color:#040a5d;">Talenteed</strong>.
    Nous avons bien reçu vos informations et notre équipe va les examiner dans les plus brefs délais.
  </p>

  <div class="info-card" style="border-left-color: #f29f1f;">
    <div class="info-card-title" style="color:#b45309;">Statut de votre compte</div>
    <div class="info-row">
      <span class="info-label">Compte</span>
      <span class="info-value">{{ $user->email }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Statut</span>
      <span class="info-value">
        <span class="status-badge status-badge--pending">En attente de vérification</span>
      </span>
    </div>
  </div>

  <p class="email-text">
    Une fois votre compte validé par notre équipe, vous recevrez un e-mail de confirmation et pourrez vous connecter à votre espace recruteur.
  </p>

  <p class="email-text" style="font-weight: 600; color: #040a5d;">Ce que vous pouvez faire une fois activé :</p>
  <ul class="email-list">
    <li>Publier vos offres d'emploi et recevoir des candidatures qualifiées</li>
    <li>Participer aux événements de speed recruiting</li>
    <li>Accéder au <strong>matching IA</strong> entre vos offres et les talents</li>
    <li>Gérer vos entretiens sur les stands en créneaux de 15 minutes</li>
  </ul>

  <div class="highlight-box">
    <div class="highlight-box-label">Délai de traitement</div>
    <div class="highlight-box-value" style="font-size:16px; color:#b45309;">Généralement sous 24 à 48 heures ouvrées</div>
  </div>

  <hr class="email-divider">
  <p class="email-text" style="font-size:13px; color:#94a3b8; text-align:center;">
    Une question ? Contactez-nous à <a href="mailto:contact@talenteed.io" style="color:#192bc2;">contact@talenteed.io</a>
  </p>
@endsection

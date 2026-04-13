@extends('emails.layout')

@section('title', 'Bienvenue sur Talenteed')
@section('header_badge', 'Nouveau compte')

@section('hero')
  <div style="text-align:center;">
    <div class="email-hero-icon" style="margin: 0 auto 14px;">
      <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
      </svg>
    </div>
    <div class="email-hero-title">Bienvenue sur Talenteed, {{ $user->name }} !</div>
    <div class="email-hero-subtitle">Votre compte talent est prêt. Trouvez votre prochain emploi.</div>
  </div>
@endsection

@section('content')
  <p class="email-greeting">Bonjour {{ $user->name }},</p>
  <p class="email-text">
    Nous sommes ravis de vous accueillir sur <strong style="color:#040a5d;">Talenteed</strong>, la plateforme de recrutement événementiel qui connecte les meilleurs talents avec des entreprises qui recrutent.
  </p>

  <p class="email-text" style="margin-bottom: 8px; font-weight: 600; color: #040a5d;">Avec votre compte, vous pouvez :</p>
  <ul class="email-list">
    <li>Consulter les offres d'emploi et postuler en ligne</li>
    <li>Participer aux événements de speed recruiting</li>
    <li>Obtenir un <strong>matching IA</strong> entre votre profil et les entreprises</li>
    <li>Réserver des créneaux d'entretien de 15 minutes sur les stands</li>
    <li>Suivre l'état de vos candidatures en temps réel</li>
  </ul>

  <div class="email-cta">
    <a href="https://talenteed.io/login" class="btn-primary">Accéder à mon espace</a>
  </div>

  <hr class="email-divider">
  <p class="email-text" style="font-size: 13px; color: #94a3b8; text-align: center;">
    Une question ? Contactez-nous à <a href="mailto:contact@talenteed.io" style="color: #192bc2;">contact@talenteed.io</a>
  </p>
@endsection

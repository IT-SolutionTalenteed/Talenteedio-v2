@extends('emails.layout')

@section('title', 'Bienvenue sur Talenteed')
@section('header_badge', 'Compte recruteur')

@section('hero')
  <div style="text-align:center;">
    <div class="email-hero-icon" style="margin: 0 auto 14px;">
      <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
      </svg>
    </div>
    <div class="email-hero-title">Bienvenue sur Talenteed, {{ $user->entreprise ?? $user->name }} !</div>
    <div class="email-hero-subtitle">Votre espace recruteur est prêt. Trouvez les meilleurs talents.</div>
  </div>
@endsection

@section('content')
  <p class="email-greeting">Bonjour {{ $user->name }},</p>
  <p class="email-text">
    Nous sommes ravis de vous accueillir sur <strong style="color:#040a5d;">Talenteed</strong>, la plateforme de recrutement événementiel qui connecte les entreprises avec les meilleurs talents.
  </p>

  <p class="email-text" style="margin-bottom: 8px; font-weight: 600; color: #040a5d;">Avec votre espace recruteur, vous pouvez :</p>
  <ul class="email-list">
    <li>Publier vos offres d'emploi et recevoir des candidatures qualifiées</li>
    <li>Participer aux événements de speed recruiting</li>
    <li>Bénéficier du <strong>matching IA</strong> entre vos offres et les talents</li>
    <li>Gérer vos entretiens et créneaux de 15 minutes sur les stands</li>
    <li>Suivre vos candidatures et recruter plus rapidement</li>
  </ul>

  <div class="email-cta">
    <a href="https://talenteed.io/login" class="btn-primary">Accéder à mon espace recruteur</a>
  </div>

  <hr class="email-divider">
  <p class="email-text" style="font-size: 13px; color: #94a3b8; text-align: center;">
    Une question ? Contactez-nous à <a href="mailto:contact@talenteed.io" style="color: #192bc2;">contact@talenteed.io</a>
  </p>
@endsection

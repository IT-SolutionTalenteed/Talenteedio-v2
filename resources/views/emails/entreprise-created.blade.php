@extends('emails.layout')

@section('title', 'Vos identifiants Talenteed')
@section('header_badge', 'Compte entreprise créé')

@section('hero')
  <div style="text-align:center;">
    <div class="email-hero-icon" style="margin: 0 auto 14px;">
      <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
      </svg>
    </div>
    <div class="email-hero-title">Bienvenue sur Talenteed, {{ $nomEntreprise }} !</div>
    <div class="email-hero-subtitle">Votre espace recruteur est prêt à l'emploi.</div>
  </div>
@endsection

@section('content')
  <p class="email-greeting">Bonjour,</p>
  <p class="email-text">
    Un compte entreprise vient d'être créé pour <strong style="color:#040a5d;">{{ $nomEntreprise }}</strong> sur la plateforme Talenteed. Vous pouvez dès maintenant accéder à votre espace recruteur.
  </p>

  <div class="info-card">
    <div class="info-card-title">Vos identifiants de connexion</div>
    <div class="info-row">
      <span class="info-label">Email</span>
      <span class="info-value">{{ $email }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Mot de passe</span>
      <span class="info-value" style="font-family: monospace; font-size: 15px; letter-spacing: 1px; color: #040a5d; font-weight: 700;">{{ $password }}</span>
    </div>
  </div>

  <div class="highlight-box">
    <div class="highlight-box-label">Sécurité</div>
    <div style="font-size: 14px; color: #475569; margin-top: 4px;">
      Nous vous recommandons de <strong>changer votre mot de passe</strong> dès votre première connexion.
    </div>
  </div>

  <div class="email-cta">
    <a href="https://talenteed.io/login" class="btn-primary">Accéder à mon espace recruteur</a>
  </div>

  <hr class="email-divider">
  <p class="email-text" style="font-size: 13px; color: #94a3b8; text-align: center;">
    Besoin d'aide ? <a href="mailto:contact@talenteed.io" style="color: #192bc2;">contact@talenteed.io</a>
  </p>
@endsection

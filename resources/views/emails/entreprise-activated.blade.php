@extends('emails.layout')

@section('title', 'Votre compte est activé !')
@section('header_badge', 'Compte recruteur')

@section('hero')
  <div style="text-align:center;">
    <div class="email-hero-icon" style="margin:0 auto 14px; background:rgba(34,197,94,0.2); border-color:rgba(34,197,94,0.4);">
      <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
      </svg>
    </div>
    <div class="email-hero-title">Votre compte recruteur est activé !</div>
    <div class="email-hero-subtitle">Bienvenue sur Talenteed — vous pouvez maintenant vous connecter et commencer à recruter.</div>
  </div>
@endsection

@section('content')
  <p class="email-greeting">Bonjour {{ $user->name }},</p>

  <p class="email-text">
    Excellente nouvelle ! Notre équipe a validé votre compte. Vous avez désormais accès à votre espace recruteur sur <strong style="color:#040a5d;">Talenteed</strong>.
  </p>

  <div class="info-card" style="border-left-color: #22c55e;">
    <div class="info-card-title" style="color:#15803d;">Statut de votre compte</div>
    <div class="info-row">
      <span class="info-label">Compte</span>
      <span class="info-value">{{ $user->email }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Statut</span>
      <span class="info-value">
        <span class="status-badge status-badge--success">Compte actif</span>
      </span>
    </div>
  </div>

  <div class="email-cta">
    <a href="{{ config('app.frontend_url', 'http://localhost:5173') }}/login" class="btn-primary">
      Accéder à mon espace recruteur
    </a>
  </div>

  <p class="email-text" style="font-weight:600; color:#040a5d;">Vous pouvez maintenant :</p>
  <ul class="email-list">
    <li>Publier vos offres d'emploi et recevoir des candidatures qualifiées</li>
    <li>Participer aux événements de speed recruiting Talenteed</li>
    <li>Accéder au <strong>matching IA</strong> entre vos offres et les talents</li>
    <li>Gérer vos entretiens sur les stands en créneaux de 15 minutes</li>
  </ul>

  <hr class="email-divider">
  <p class="email-text" style="font-size:13px; color:#94a3b8; text-align:center;">
    Une question ? Contactez-nous à <a href="mailto:contact@talenteed.io" style="color:#192bc2;">contact@talenteed.io</a>
  </p>
@endsection

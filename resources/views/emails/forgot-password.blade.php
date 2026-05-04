@extends('emails.layout')

@section('title', 'Réinitialisation de mot de passe')
@section('header_badge', 'Sécurité du compte')

@section('hero')
  <div style="text-align:center;">
    <div class="email-hero-icon" style="margin:0 auto 14px; background:rgba(25,43,194,0.15); border-color:rgba(25,43,194,0.35);">
      <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#192bc2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
      </svg>
    </div>
    <div class="email-hero-title">Réinitialisation de votre mot de passe</div>
    <div class="email-hero-subtitle">Vous avez demandé à réinitialiser votre mot de passe. Ce lien est valable 1 heure.</div>
  </div>
@endsection

@section('content')
  <p class="email-greeting">Bonjour {{ $user->name }},</p>

  <p class="email-text">
    Nous avons reçu une demande de réinitialisation du mot de passe associé à votre compte <strong style="color:#040a5d;">Talenteed</strong>.
  </p>

  <div class="email-cta">
    <a href="{{ $resetUrl }}" class="btn-primary">
      Réinitialiser mon mot de passe
    </a>
  </div>

  <div class="info-card" style="border-left-color: #192bc2;">
    <div class="info-card-title" style="color:#040a5d;">Informations importantes</div>
    <div class="info-row">
      <span class="info-label">Compte</span>
      <span class="info-value">{{ $user->email }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Validité</span>
      <span class="info-value">1 heure</span>
    </div>
  </div>

  <p class="email-text">
    Si vous n'avez pas fait cette demande, ignorez cet email. Votre mot de passe restera inchangé.
  </p>

  <p class="email-text" style="font-size:12px; color:#94a3b8; word-break:break-all;">
    Si le bouton ne fonctionne pas, copiez ce lien dans votre navigateur :<br>
    <a href="{{ $resetUrl }}" style="color:#192bc2;">{{ $resetUrl }}</a>
  </p>

  <hr class="email-divider">
  <p class="email-text" style="font-size:13px; color:#94a3b8; text-align:center;">
    Une question ? Contactez-nous à <a href="mailto:contact@talenteed.io" style="color:#192bc2;">contact@talenteed.io</a>
  </p>
@endsection

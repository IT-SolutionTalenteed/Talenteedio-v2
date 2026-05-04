@extends('emails.layout')

@section('title', 'Mot de passe modifié')
@section('header_badge', 'Sécurité du compte')

@section('hero')
  <div style="text-align:center;">
    <div class="email-hero-icon" style="margin:0 auto 14px; background:rgba(34,197,94,0.2); border-color:rgba(34,197,94,0.4);">
      <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
      </svg>
    </div>
    <div class="email-hero-title">Mot de passe modifié avec succès</div>
    <div class="email-hero-subtitle">La sécurité de votre compte a été mise à jour.</div>
  </div>
@endsection

@section('content')
  <p class="email-greeting">Bonjour {{ $user->name }},</p>

  <p class="email-text">
    Votre mot de passe <strong style="color:#040a5d;">Talenteed</strong> a bien été modifié le {{ now()->format('d/m/Y à H:i') }}.
  </p>

  <div class="info-card" style="border-left-color: #22c55e;">
    <div class="info-card-title" style="color:#15803d;">Détails de la modification</div>
    <div class="info-row">
      <span class="info-label">Compte</span>
      <span class="info-value">{{ $user->email }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Date</span>
      <span class="info-value">{{ now()->format('d/m/Y à H:i') }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Statut</span>
      <span class="info-value">
        <span class="status-badge status-badge--success">Mot de passe mis à jour</span>
      </span>
    </div>
  </div>

  <p class="email-text">
    Si vous n'êtes pas à l'origine de cette modification, contactez-nous immédiatement à
    <a href="mailto:contact@talenteed.io" style="color:#192bc2;">contact@talenteed.io</a>
    afin de sécuriser votre compte.
  </p>

  <hr class="email-divider">
  <p class="email-text" style="font-size:13px; color:#94a3b8; text-align:center;">
    Une question ? Contactez-nous à <a href="mailto:contact@talenteed.io" style="color:#192bc2;">contact@talenteed.io</a>
  </p>
@endsection

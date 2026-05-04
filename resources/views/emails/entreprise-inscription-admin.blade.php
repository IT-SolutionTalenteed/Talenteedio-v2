@extends('emails.layout')

@section('title', 'Nouvelle inscription entreprise')
@section('header_badge', 'Notification Admin')

@section('hero')
  <div style="text-align:center;">
    <div class="email-hero-icon" style="margin:0 auto 14px; background:rgba(25,43,194,0.15); border-color:rgba(25,43,194,0.35);">
      <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#192bc2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
      </svg>
    </div>
    <div class="email-hero-title">Nouvelle inscription entreprise</div>
    <div class="email-hero-subtitle">Une entreprise vient de s'inscrire et attend votre validation.</div>
  </div>
@endsection

@section('content')
  <p class="email-greeting">Bonjour,</p>

  <p class="email-text">
    Une nouvelle entreprise s'est inscrite sur <strong style="color:#040a5d;">Talenteed</strong> via la page d'inscription. Son compte est actuellement <strong>en attente de validation</strong>.
  </p>

  <div class="info-card" style="border-left-color: #192bc2;">
    <div class="info-card-title" style="color:#040a5d;">Détails de l'inscription</div>
    <div class="info-row">
      <span class="info-label">Entreprise</span>
      <span class="info-value">{{ $nomEntreprise }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Email</span>
      <span class="info-value">{{ $entrepriseUser->email }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Date</span>
      <span class="info-value">{{ now()->format('d/m/Y à H:i') }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Statut</span>
      <span class="info-value">
        <span class="status-badge status-badge--pending">En attente de validation</span>
      </span>
    </div>
  </div>

  <p class="email-text">
    Rendez-vous dans l'interface d'administration pour examiner le dossier et activer le compte.
  </p>

  <div class="email-cta">
    <a href="{{ $adminUrl }}" class="btn-primary">
      Valider le compte dans l'admin
    </a>
  </div>

  <hr class="email-divider">
  <p class="email-text" style="font-size:13px; color:#94a3b8; text-align:center;">
    Cet email est envoyé automatiquement à chaque inscription entreprise.
  </p>
@endsection

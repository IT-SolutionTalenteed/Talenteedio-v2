@extends('emails.layout')

@section('title', 'Rappel — Votre entretien dans 1 heure')
@section('header_badge', 'Rappel entretien')

@section('hero')
  <div style="text-align:center;">
    <div class="email-hero-icon" style="margin: 0 auto 14px; background: rgba(242,159,31,0.2); border-color: rgba(242,159,31,0.5);">
      <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#fbbf24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
      </svg>
    </div>
    <div class="email-hero-title">Votre entretien commence dans 1 heure !</div>
    <div class="email-hero-subtitle">Préparez-vous — c'est bientôt votre moment.</div>
  </div>
@endsection

@section('content')
  <p class="email-greeting">Bonjour {{ $entretien->talent->name }},</p>
  <p class="email-text">
    N'oubliez pas votre entretien prévu aujourd'hui avec <strong style="color:#040a5d;">{{ $entretien->entreprise->nom }}</strong>. Voici les informations importantes à avoir sous la main.
  </p>

  <div class="highlight-box">
    <div class="highlight-box-label">Créneau dans 1 heure</div>
    <div class="highlight-box-value">{{ substr($entretien->heure_debut, 0, 5) }} – {{ substr($entretien->heure_fin, 0, 5) }}</div>
  </div>

  <div class="info-card">
    <div class="info-card-title">Détails de l'entretien</div>
    <div class="info-row">
      <span class="info-label">Entreprise</span>
      <span class="info-value" style="font-weight: 700; color: #040a5d;">{{ $entretien->entreprise->nom }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Événement</span>
      <span class="info-value">{{ $entretien->evenement->titre }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Heure</span>
      <span class="info-value" style="font-weight: 700; color: #040a5d;">{{ substr($entretien->heure_debut, 0, 5) }} – {{ substr($entretien->heure_fin, 0, 5) }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Durée</span>
      <span class="info-value">15 minutes</span>
    </div>
  </div>

  <p class="email-text" style="font-size: 13px; color: #64748b;">
    Conseils : présentez-vous quelques minutes avant, ayez votre CV à portée de main, et préparez un pitch de 2 minutes sur votre parcours.
  </p>

  <div class="email-cta">
    <a href="https://talenteed.io/login" class="btn-primary">Voir mes entretiens</a>
  </div>

  <hr class="email-divider">
  <p class="email-text" style="font-size: 13px; color: #94a3b8; text-align: center;">
    Bonne chance ! — L'équipe Talenteed
  </p>
@endsection

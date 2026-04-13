@extends('emails.layout')

@section('title', 'Nouvelle demande de participation — Talenteed')
@section('header_badge', 'Admin — Nouvelle demande')

@section('hero')
  <div style="text-align:center;">
    <div class="email-hero-icon" style="margin: 0 auto 14px;">
      <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
      </svg>
    </div>
    <div class="email-hero-title">Nouvelle demande de participation</div>
    <div class="email-hero-subtitle">Une entreprise souhaite rejoindre un événement.</div>
  </div>
@endsection

@section('content')
  <p class="email-greeting">Bonjour,</p>
  <p class="email-text">
    L'entreprise <strong style="color:#040a5d;">{{ $nomEntreprise }}</strong> souhaite participer à l'événement <strong style="color:#040a5d;">{{ $nomEvenement }}</strong>. Connectez-vous à l'espace administrateur pour traiter cette demande.
  </p>

  <div class="info-card">
    <div class="info-card-title">Détails de la demande</div>
    <div class="info-row">
      <span class="info-label">Entreprise</span>
      <span class="info-value" style="font-weight: 700; color: #040a5d;">{{ $nomEntreprise }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Événement</span>
      <span class="info-value">{{ $nomEvenement }}</span>
    </div>
    @if($messageEntreprise)
    <div class="info-row" style="flex-direction: column; gap: 6px;">
      <span class="info-label">Message</span>
      <span class="info-value" style="font-style: italic; color: #64748b;">"{{ $messageEntreprise }}"</span>
    </div>
    @endif
  </div>

  <div class="email-cta">
    <a href="https://talenteed.io/admin" class="btn-primary">Traiter la demande</a>
  </div>

  <hr class="email-divider">
  <p class="email-text" style="font-size: 13px; color: #94a3b8; text-align: center;">
    Notification automatique — Talenteed Admin
  </p>
@endsection

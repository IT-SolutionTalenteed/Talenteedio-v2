@extends('emails.layout')

@section('title', 'Réservation d\'entretien — Talenteed')
@section('header_badge', 'Entretien réservé')

@section('hero')
  <div style="text-align:center;">
    <div class="email-hero-icon" style="margin: 0 auto 14px;">
      <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
      </svg>
    </div>
    @if($destinataire === 'talent')
      <div class="email-hero-title">Votre entretien a été réservé !</div>
      <div class="email-hero-subtitle">En attente de confirmation de l'entreprise.</div>
    @elseif($destinataire === 'entreprise')
      <div class="email-hero-title">Nouvelle demande d'entretien</div>
      <div class="email-hero-subtitle">Un talent souhaite vous rencontrer sur votre stand.</div>
    @else
      <div class="email-hero-title">Nouvelle réservation d'entretien</div>
      <div class="email-hero-subtitle">Notification administrateur.</div>
    @endif
  </div>
@endsection

@section('content')
  @if($destinataire === 'talent')
    <p class="email-greeting">Bonjour {{ $entretien->talent->name }},</p>
    <p class="email-text">
      Votre demande d'entretien a bien été transmise à <strong style="color:#040a5d;">{{ $entretien->entreprise->nom }}</strong>. Vous recevrez une notification dès que l'entreprise aura répondu.
    </p>
  @elseif($destinataire === 'entreprise')
    <p class="email-greeting">Bonjour,</p>
    <p class="email-text">
      Le talent <strong style="color:#040a5d;">{{ $entretien->talent->name }}</strong> souhaite vous rencontrer lors de l'événement. Connectez-vous à votre espace pour confirmer ou refuser cet entretien.
    </p>
  @else
    <p class="email-greeting">Notification admin</p>
    <p class="email-text">
      Le talent <strong style="color:#040a5d;">{{ $entretien->talent->name }}</strong> a réservé un entretien avec <strong style="color:#040a5d;">{{ $entretien->entreprise->nom }}</strong>.
    </p>
  @endif

  <div class="info-card">
    <div class="info-card-title">Détails de l'entretien</div>
    <div class="info-row">
      <span class="info-label">Événement</span>
      <span class="info-value">{{ $entretien->evenement->titre }}</span>
    </div>
    @if($destinataire === 'talent')
    <div class="info-row">
      <span class="info-label">Entreprise</span>
      <span class="info-value">{{ $entretien->entreprise->nom }}</span>
    </div>
    @elseif($destinataire === 'entreprise')
    <div class="info-row">
      <span class="info-label">Talent</span>
      <span class="info-value">{{ $entretien->talent->name }}</span>
    </div>
    @endif
    <div class="info-row">
      <span class="info-label">Date</span>
      <span class="info-value">{{ \Carbon\Carbon::parse($entretien->date)->translatedFormat('l d F Y') }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Créneau</span>
      <span class="info-value" style="font-weight: 700; color: #040a5d;">{{ substr($entretien->heure_debut, 0, 5) }} – {{ substr($entretien->heure_fin, 0, 5) }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Statut</span>
      <span class="info-value"><span class="status-badge status-badge--pending">En attente de confirmation</span></span>
    </div>
  </div>

  <div class="email-cta">
    @if($destinataire === 'entreprise')
      <a href="https://talenteed.io/login" class="btn-primary">Répondre à la demande</a>
    @else
      <a href="https://talenteed.io/login" class="btn-secondary">Voir mes entretiens</a>
    @endif
  </div>

  <hr class="email-divider">
  <p class="email-text" style="font-size: 13px; color: #94a3b8; text-align: center;">
    L'équipe Talenteed &bull; <a href="mailto:contact@talenteed.io" style="color: #192bc2;">contact@talenteed.io</a>
  </p>
@endsection

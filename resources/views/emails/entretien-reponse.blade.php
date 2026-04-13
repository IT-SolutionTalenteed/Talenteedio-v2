@extends('emails.layout')

@section('title', 'Réponse à votre entretien — Talenteed')
@section('header_badge', $entretien->statut === 'confirme' ? 'Entretien confirmé' : 'Entretien refusé')

@section('hero')
  <div style="text-align:center;">
    @if($entretien->statut === 'confirme')
      <div class="email-hero-icon" style="margin: 0 auto 14px; background: rgba(34,197,94,0.2); border-color: rgba(34,197,94,0.4);">
        <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="20 6 9 17 4 12"/>
        </svg>
      </div>
      <div class="email-hero-title">Votre entretien est confirmé !</div>
      <div class="email-hero-subtitle">{{ $entretien->entreprise->nom }} vous attend. Préparez-vous !</div>
    @else
      <div class="email-hero-icon" style="margin: 0 auto 14px; background: rgba(239,68,68,0.2); border-color: rgba(239,68,68,0.4);">
        <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
      </div>
      <div class="email-hero-title">Entretien non disponible</div>
      <div class="email-hero-subtitle">L'entreprise n'est pas disponible à ce créneau.</div>
    @endif
  </div>
@endsection

@section('content')
  <p class="email-greeting">Bonjour {{ $entretien->talent->name }},</p>

  @if($entretien->statut === 'confirme')
    <p class="email-text">
      Bonne nouvelle ! <strong style="color:#040a5d;">{{ $entretien->entreprise->nom }}</strong> a confirmé votre entretien. Retrouvez ci-dessous tous les détails pour vous préparer au mieux.
    </p>

    <div class="highlight-box">
      <div class="highlight-box-label">Créneau confirmé</div>
      <div class="highlight-box-value">{{ substr($entretien->heure_debut, 0, 5) }} – {{ substr($entretien->heure_fin, 0, 5) }}</div>
      <div style="font-size: 13px; color: #64748b; margin-top: 4px;">{{ \Carbon\Carbon::parse($entretien->date)->translatedFormat('l d F Y') }}</div>
    </div>
  @else
    <p class="email-text">
      <strong style="color:#040a5d;">{{ $entretien->entreprise->nom }}</strong> n'est malheureusement pas disponible sur ce créneau. Vous pouvez retourner sur votre espace pour choisir un autre horaire.
    </p>
  @endif

  <div class="info-card">
    <div class="info-card-title">Récapitulatif</div>
    <div class="info-row">
      <span class="info-label">Entreprise</span>
      <span class="info-value">{{ $entretien->entreprise->nom }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Événement</span>
      <span class="info-value">{{ $entretien->evenement->titre }}</span>
    </div>
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
      <span class="info-value">
        @if($entretien->statut === 'confirme')
          <span class="status-badge status-badge--success">Confirmé</span>
        @else
          <span class="status-badge status-badge--danger">Refusé</span>
        @endif
      </span>
    </div>
  </div>

  <div class="email-cta">
    @if($entretien->statut === 'confirme')
      <a href="https://talenteed.io/login" class="btn-primary">Voir mes entretiens</a>
    @else
      <a href="https://talenteed.io/login" class="btn-secondary">Choisir un autre créneau</a>
    @endif
  </div>

  <hr class="email-divider">
  <p class="email-text" style="font-size: 13px; color: #94a3b8; text-align: center;">
    L'équipe Talenteed &bull; <a href="mailto:contact@talenteed.io" style="color: #192bc2;">contact@talenteed.io</a>
  </p>
@endsection

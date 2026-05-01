@extends('emails.layout')

@section('header_badge', 'Nouvelle Candidature')

@section('hero')
  <div class="email-hero-icon">
    <svg width="32" height="32" fill="white" viewBox="0 0 24 24">
      <path d="M9 11H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm2-7h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11z"/>
    </svg>
  </div>
  <div class="email-hero-title">Nouvelle candidature validée</div>
  <div class="email-hero-subtitle">Un talent hautement compatible a postulé</div>
@endsection

@section('content')
  <p class="email-greeting">Bonjour {{ $candidature->offre->entreprise->nom }},</p>

  <p class="email-text">
    Excellente nouvelle ! Vous avez reçu une nouvelle candidature pour votre offre <strong>{{ $candidature->offre->titre }}</strong>.
  </p>

  <p class="email-text">
    Cette candidature a été <strong>automatiquement validée</strong> grâce à notre système de matching intelligent basé sur l'IA, 
    qui a détecté une excellente compatibilité entre le profil du candidat et vos critères.
  </p>

  <!-- Score de matching -->
  <div class="highlight-box">
    <div class="highlight-box-label">Score de compatibilité</div>
    <div class="highlight-box-value">{{ $matchingScore }}%</div>
  </div>

  <!-- Informations du candidat -->
  <div class="info-card">
    <div class="info-card-title">Informations du candidat</div>
    <div class="info-row">
      <span class="info-label">Nom</span>
      <span class="info-value">{{ $candidature->talent->name }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Email</span>
      <span class="info-value">{{ $candidature->talent->email }}</span>
    </div>
    @if($candidature->talent->telephone)
    <div class="info-row">
      <span class="info-label">Téléphone</span>
      <span class="info-value">{{ $candidature->talent->telephone }}</span>
    </div>
    @endif
    @if($candidature->talent->ville || $candidature->talent->pays)
    <div class="info-row">
      <span class="info-label">Localisation</span>
      <span class="info-value">{{ implode(', ', array_filter([$candidature->talent->ville, $candidature->talent->pays])) }}</span>
    </div>
    @endif
    <div class="info-row">
      <span class="info-label">Date de candidature</span>
      <span class="info-value">{{ $candidature->created_at->format('d/m/Y à H:i') }}</span>
    </div>
  </div>

  @if($candidature->message)
  <div class="info-card">
    <div class="info-card-title">Message du candidat</div>
    <p class="email-text" style="margin: 0;">{{ $candidature->message }}</p>
  </div>
  @endif

  <p class="email-text">
    Le CV du candidat est joint à cet email. Nous vous recommandons de le contacter rapidement pour planifier un entretien.
  </p>

  <div class="email-cta">
    <a href="{{ config('app.frontend_url') }}/entreprise/candidatures/{{ $candidature->id }}" class="btn-primary">
      Voir la candidature complète
    </a>
  </div>

  <hr class="email-divider">

  <p class="email-text" style="font-size: 13px; color: #64748b;">
    <strong>Pourquoi cette candidature a été validée automatiquement ?</strong><br>
    Notre système d'IA a analysé le CV du candidat et a détecté un score de compatibilité de {{ $matchingScore }}%, 
    ce qui indique une excellente adéquation avec vos critères (compétences, expérience, secteur, localisation).
  </p>
@endsection

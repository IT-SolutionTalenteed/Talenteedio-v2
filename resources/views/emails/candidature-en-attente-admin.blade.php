@extends('emails.layout')

@section('header_badge', 'Action Requise')

@section('hero')
  <div class="email-hero-icon">
    <svg width="32" height="32" fill="white" viewBox="0 0 24 24">
      <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
    </svg>
  </div>
  <div class="email-hero-title">Candidature à valider</div>
  <div class="email-hero-subtitle">Score de matching inférieur au seuil automatique</div>
@endsection

@section('content')
  <p class="email-greeting">Bonjour Admin,</p>

  <p class="email-text">
    Une nouvelle candidature nécessite votre validation avant d'être transmise à l'entreprise.
  </p>

  <!-- Score de matching -->
  <div class="highlight-box" style="background: linear-gradient(135deg, rgba(245,158,11,0.08), rgba(245,158,11,0.15)); border-color: rgba(245,158,11,0.35);">
    <div class="highlight-box-label" style="color: #b45309;">Score de compatibilité</div>
    <div class="highlight-box-value">{{ $matchingScore }}%</div>
  </div>

  <p class="email-text">
    Le score de matching est inférieur à 80%, ce qui nécessite une vérification manuelle avant transmission.
  </p>

  <!-- Informations de la candidature -->
  <div class="info-card">
    <div class="info-card-title">Informations de la candidature</div>
    <div class="info-row">
      <span class="info-label">Candidat</span>
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
    <div class="info-row">
      <span class="info-label">Offre</span>
      <span class="info-value">{{ $candidature->offre->titre }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Entreprise</span>
      <span class="info-value">{{ $candidature->offre->entreprise->nom }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Date</span>
      <span class="info-value">{{ $candidature->created_at->format('d/m/Y à H:i') }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Statut</span>
      <span class="info-value"><span class="status-badge status-badge--warning">En attente de validation</span></span>
    </div>
  </div>

  @if($candidature->message)
  <div class="info-card">
    <div class="info-card-title">Message du candidat</div>
    <p class="email-text" style="margin: 0;">{{ $candidature->message }}</p>
  </div>
  @endif

  <p class="email-text">
    Le CV du candidat est joint à cet email. Veuillez examiner la candidature et décider de la valider ou de la rejeter.
  </p>

  <div class="email-cta">
    <a href="{{ config('app.frontend_url') }}/admin/candidatures/{{ $candidature->id }}" class="btn-primary">
      Examiner la candidature
    </a>
  </div>

  <hr class="email-divider">

  <p class="email-text" style="font-size: 13px; color: #64748b;">
    <strong>Actions possibles :</strong>
  </p>

  <ul class="email-list" style="font-size: 13px;">
    <li><strong>Valider</strong> : La candidature sera transmise à l'entreprise</li>
    <li><strong>Rejeter</strong> : Le candidat sera notifié que sa candidature n'a pas été retenue</li>
  </ul>

  <p class="email-text" style="font-size: 13px; color: #64748b;">
    <strong>Pourquoi cette candidature nécessite une validation ?</strong><br>
    Le score de matching de {{ $matchingScore }}% est inférieur au seuil de validation automatique (80%). 
    Cela peut indiquer un décalage entre le profil du candidat et les critères de l'offre, mais une vérification 
    manuelle peut révéler des compétences ou expériences pertinentes non détectées automatiquement.
  </p>
@endsection

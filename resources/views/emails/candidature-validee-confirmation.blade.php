@extends('emails.layout')

@section('header_badge', 'Candidature Envoyée')

@section('hero')
  <div class="email-hero-icon">
    <svg width="32" height="32" fill="white" viewBox="0 0 24 24">
      <path d="M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z"/>
    </svg>
  </div>
  <div class="email-hero-title">Candidature envoyée avec succès !</div>
  <div class="email-hero-subtitle">Votre profil a été transmis à l'entreprise</div>
@endsection

@section('content')
  <p class="email-greeting">Bonjour {{ $candidature->talent->name }},</p>

  <p class="email-text">
    Félicitations ! Votre candidature pour le poste de <strong>{{ $candidature->offre->titre }}</strong> 
    chez <strong>{{ $candidature->offre->entreprise->nom }}</strong> a été envoyée avec succès.
  </p>

  <!-- Score de matching -->
  <div class="highlight-box">
    <div class="highlight-box-label">Votre score de compatibilité</div>
    <div class="highlight-box-value">{{ $matchingScore }}%</div>
  </div>

  <p class="email-text">
    <strong>Excellente nouvelle !</strong> Votre profil présente une très forte compatibilité avec cette offre. 
    Votre candidature a été <strong>automatiquement validée</strong> et transmise directement à l'entreprise.
  </p>

  <!-- Informations de l'offre -->
  <div class="info-card">
    <div class="info-card-title">Détails de l'offre</div>
    <div class="info-row">
      <span class="info-label">Poste</span>
      <span class="info-value">{{ $candidature->offre->titre }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Entreprise</span>
      <span class="info-value">{{ $candidature->offre->entreprise->nom }}</span>
    </div>
    @if($candidature->offre->localisation)
    <div class="info-row">
      <span class="info-label">Localisation</span>
      <span class="info-value">{{ $candidature->offre->localisation }}</span>
    </div>
    @endif
    <div class="info-row">
      <span class="info-label">Date de candidature</span>
      <span class="info-value">{{ $candidature->created_at->format('d/m/Y à H:i') }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Statut</span>
      <span class="info-value"><span class="status-badge status-badge--success">Envoyée à l'entreprise</span></span>
    </div>
  </div>

  <p class="email-text">
    <strong>Prochaines étapes :</strong>
  </p>

  <ul class="email-list">
    <li>L'entreprise va examiner votre candidature dans les prochains jours</li>
    <li>Si votre profil les intéresse, ils vous contacteront directement</li>
    <li>Vous pouvez suivre l'évolution de votre candidature depuis votre espace personnel</li>
  </ul>

  <div class="email-cta">
    <a href="{{ config('app.frontend_url') }}/mes-candidatures" class="btn-primary">
      Suivre ma candidature
    </a>
  </div>

  <hr class="email-divider">

  <p class="email-text" style="font-size: 13px; color: #64748b;">
    <strong>Pourquoi ma candidature a été validée automatiquement ?</strong><br>
    Votre score de compatibilité de {{ $matchingScore }}% indique une excellente adéquation avec les critères de l'offre. 
    Notre système d'IA a analysé votre CV et a détecté que vous correspondez parfaitement au profil recherché.
  </p>

  <p class="email-text" style="font-size: 13px; color: #64748b;">
    Nous vous souhaitons bonne chance pour la suite du processus de recrutement ! 🍀
  </p>
@endsection

@extends('emails.layout')

@section('header_badge', 'Candidature Reçue')

@section('hero')
  <div class="email-hero-icon">
    <svg width="32" height="32" fill="white" viewBox="0 0 24 24">
      <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
    </svg>
  </div>
  <div class="email-hero-title">Candidature bien reçue</div>
  <div class="email-hero-subtitle">En cours de validation</div>
@endsection

@section('content')
  <p class="email-greeting">Bonjour {{ $candidature->talent->name }},</p>

  <p class="email-text">
    Merci d'avoir postulé pour le poste de <strong>{{ $candidature->offre->titre }}</strong> 
    chez <strong>{{ $candidature->offre->entreprise->nom }}</strong>.
  </p>

  <p class="email-text">
    Votre candidature a bien été enregistrée et est actuellement <strong>en cours de validation</strong> par notre équipe.
  </p>

  <!-- Score de matching -->
  <div class="highlight-box" style="background: linear-gradient(135deg, rgba(14,165,233,0.08), rgba(14,165,233,0.15)); border-color: rgba(14,165,233,0.35);">
    <div class="highlight-box-label" style="color: #0369a1;">Votre score de compatibilité</div>
    <div class="highlight-box-value">{{ $matchingScore }}%</div>
  </div>

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
      <span class="info-value"><span class="status-badge status-badge--pending">En attente de validation</span></span>
    </div>
  </div>

  <p class="email-text">
    <strong>Que se passe-t-il maintenant ?</strong>
  </p>

  <ul class="email-list">
    <li>Notre équipe va examiner votre candidature dans les 24-48 heures</li>
    <li>Une fois validée, elle sera transmise directement à l'entreprise</li>
    <li>Vous recevrez une notification par email dès que votre candidature sera validée</li>
    <li>Vous pouvez suivre l'évolution depuis votre espace personnel</li>
  </ul>

  <div class="email-cta">
    <a href="{{ config('app.frontend_url') }}/mes-candidatures" class="btn-primary">
      Suivre ma candidature
    </a>
  </div>

  <hr class="email-divider">

  <p class="email-text" style="font-size: 13px; color: #64748b;">
    <strong>Pourquoi ma candidature doit être validée ?</strong><br>
    Pour garantir la qualité des candidatures envoyées aux entreprises, notre équipe effectue une vérification 
    rapide avant transmission. Cela permet d'optimiser vos chances de succès.
  </p>

  <p class="email-text" style="font-size: 13px; color: #64748b;">
    Merci de votre patience et bonne chance pour la suite ! 🍀
  </p>
@endsection

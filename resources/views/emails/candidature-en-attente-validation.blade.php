@extends('emails.layout')

@section('header_badge', __('emails.pending_validation.badge'))

@section('hero')
  <div class="email-hero-icon">
    <svg width="32" height="32" fill="white" viewBox="0 0 24 24">
      <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
    </svg>
  </div>
  <div class="email-hero-title">{{ __('emails.pending_validation.hero_title') }}</div>
  <div class="email-hero-subtitle">{{ __('emails.pending_validation.hero_subtitle') }}</div>
@endsection

@section('content')
  <p class="email-greeting">{!! __('emails.pending_validation.greeting', ['name' => $candidature->talent->name]) !!}</p>

  <p class="email-text">
    {!! __('emails.pending_validation.intro', ['job_title' => $candidature->offre->titre, 'company' => $candidature->offre->entreprise->nom]) !!}
  </p>

  <p class="email-text">
    {!! __('emails.pending_validation.registered') !!}
  </p>

  <!-- Score de matching -->
  <div class="highlight-box" style="background: linear-gradient(135deg, rgba(14,165,233,0.08), rgba(14,165,233,0.15)); border-color: rgba(14,165,233,0.35);">
    <div class="highlight-box-label" style="color: #0369a1;">{{ __('emails.pending_validation.your_score') }}</div>
    <div class="highlight-box-value">{{ $matchingScore }}%</div>
  </div>

  <!-- Informations de l'offre -->
  <div class="info-card">
    <div class="info-card-title">{{ __('emails.pending_validation.offer_details') }}</div>
    <div class="info-row">
      <span class="info-label">{{ __('emails.pending_validation.position') }}</span>
      <span class="info-value">{{ $candidature->offre->titre }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">{{ __('emails.pending_validation.company') }}</span>
      <span class="info-value">{{ $candidature->offre->entreprise->nom }}</span>
    </div>
    @if($candidature->offre->localisation)
    <div class="info-row">
      <span class="info-label">{{ __('emails.pending_validation.location') }}</span>
      <span class="info-value">{{ $candidature->offre->localisation }}</span>
    </div>
    @endif
    <div class="info-row">
      <span class="info-label">{{ __('emails.pending_validation.application_date') }}</span>
      <span class="info-value">{{ $candidature->created_at->format('d/m/Y à H:i') }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">{{ __('emails.pending_validation.status') }}</span>
      <span class="info-value"><span class="status-badge status-badge--pending">{{ __('emails.pending_validation.status_pending') }}</span></span>
    </div>
  </div>

  <p class="email-text">
    <strong>{{ __('emails.pending_validation.what_happens') }}</strong>
  </p>

  <ul class="email-list">
    <li>{{ __('emails.pending_validation.step_1') }}</li>
    <li>{{ __('emails.pending_validation.step_2') }}</li>
    <li>{{ __('emails.pending_validation.step_3') }}</li>
    <li>{{ __('emails.pending_validation.step_4') }}</li>
  </ul>

  <div class="email-cta">
    <a href="{{ config('app.frontend_url') }}/talent/candidatures" class="btn-primary">
      {{ __('emails.pending_validation.track_application') }}
    </a>
  </div>

  <hr class="email-divider">

  <p class="email-text" style="font-size: 13px; color: #64748b;">
    <strong>{{ __('emails.pending_validation.why_validation_title') }}</strong><br>
    {{ __('emails.pending_validation.why_validation_text') }}
  </p>

  <p class="email-text" style="font-size: 13px; color: #64748b;">
    {{ __('emails.pending_validation.thank_you') }}
  </p>
@endsection

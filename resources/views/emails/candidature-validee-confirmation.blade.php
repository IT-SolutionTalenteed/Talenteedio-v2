@extends('emails.layout')

@section('header_badge', __('emails.validated_confirmation.badge'))

@section('hero')
  <div class="email-hero-icon">
    <svg width="32" height="32" fill="white" viewBox="0 0 24 24">
      <path d="M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z"/>
    </svg>
  </div>
  <div class="email-hero-title">{{ __('emails.validated_confirmation.hero_title') }}</div>
  <div class="email-hero-subtitle">{{ __('emails.validated_confirmation.hero_subtitle') }}</div>
@endsection

@section('content')
  <p class="email-greeting">{!! __('emails.validated_confirmation.greeting', ['name' => $candidature->talent->name]) !!}</p>

  <p class="email-text">
    {!! __('emails.validated_confirmation.intro', ['job_title' => $candidature->offre->titre, 'company' => $candidature->offre->entreprise->nom]) !!}
  </p>

  <!-- Score de matching -->
  <div class="highlight-box">
    <div class="highlight-box-label">{{ __('emails.validated_confirmation.your_score') }}</div>
    <div class="highlight-box-value">{{ $matchingScore }}%</div>
  </div>

  <p class="email-text">
    {!! __('emails.validated_confirmation.excellent_news') !!}
  </p>

  <!-- Informations de l'offre -->
  <div class="info-card">
    <div class="info-card-title">{{ __('emails.validated_confirmation.offer_details') }}</div>
    <div class="info-row">
      <span class="info-label">{{ __('emails.validated_confirmation.position') }}</span>
      <span class="info-value">{{ $candidature->offre->titre }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">{{ __('emails.validated_confirmation.company') }}</span>
      <span class="info-value">{{ $candidature->offre->entreprise->nom }}</span>
    </div>
    @if($candidature->offre->localisation)
    <div class="info-row">
      <span class="info-label">{{ __('emails.validated_confirmation.location') }}</span>
      <span class="info-value">{{ $candidature->offre->localisation }}</span>
    </div>
    @endif
    <div class="info-row">
      <span class="info-label">{{ __('emails.validated_confirmation.application_date') }}</span>
      <span class="info-value">{{ $candidature->created_at->format('d/m/Y à H:i') }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">{{ __('emails.validated_confirmation.status') }}</span>
      <span class="info-value"><span class="status-badge status-badge--success">{{ __('emails.validated_confirmation.status_sent') }}</span></span>
    </div>
  </div>

  <p class="email-text">
    <strong>{{ __('emails.validated_confirmation.next_steps') }}</strong>
  </p>

  <ul class="email-list">
    <li>{{ __('emails.validated_confirmation.step_1') }}</li>
    <li>{{ __('emails.validated_confirmation.step_2') }}</li>
    <li>{{ __('emails.validated_confirmation.step_3') }}</li>
  </ul>

  <div class="email-cta">
    <a href="{{ config('app.frontend_url') }}/talent/candidatures" class="btn-primary">
      {{ __('emails.validated_confirmation.track_application') }}
    </a>
  </div>

  <hr class="email-divider">

  <p class="email-text" style="font-size: 13px; color: #64748b;">
    <strong>{{ __('emails.validated_confirmation.why_validated_title') }}</strong><br>
    {!! __('emails.validated_confirmation.why_validated_text', ['score' => $matchingScore]) !!}
  </p>

  <p class="email-text" style="font-size: 13px; color: #64748b;">
    {{ __('emails.validated_confirmation.good_luck') }}
  </p>
@endsection

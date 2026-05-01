@extends('emails.layout')

@section('header_badge', __('emails.pending_admin.badge'))

@section('hero')
  <div class="email-hero-icon">
    <svg width="32" height="32" fill="white" viewBox="0 0 24 24">
      <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
    </svg>
  </div>
  <div class="email-hero-title">{{ __('emails.pending_admin.hero_title') }}</div>
  <div class="email-hero-subtitle">{{ __('emails.pending_admin.hero_subtitle') }}</div>
@endsection

@section('content')
  <p class="email-greeting">{{ __('emails.pending_admin.greeting') }}</p>

  <p class="email-text">
    {{ __('emails.pending_admin.intro') }}
  </p>

  <!-- Score de matching -->
  <div class="highlight-box" style="background: linear-gradient(135deg, rgba(245,158,11,0.08), rgba(245,158,11,0.15)); border-color: rgba(245,158,11,0.35);">
    <div class="highlight-box-label" style="color: #b45309;">{{ __('emails.pending_admin.compatibility_score') }}</div>
    <div class="highlight-box-value">{{ $matchingScore }}%</div>
  </div>

  <p class="email-text">
    {{ __('emails.pending_admin.below_threshold') }}
  </p>

  <!-- Informations de la candidature -->
  <div class="info-card">
    <div class="info-card-title">{{ __('emails.pending_admin.application_info') }}</div>
    <div class="info-row">
      <span class="info-label">{{ __('emails.pending_admin.candidate') }}</span>
      <span class="info-value">{{ $candidature->talent->name }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">{{ __('emails.pending_admin.email') }}</span>
      <span class="info-value">{{ $candidature->talent->email }}</span>
    </div>
    @if($candidature->talent->telephone)
    <div class="info-row">
      <span class="info-label">{{ __('emails.pending_admin.phone') }}</span>
      <span class="info-value">{{ $candidature->talent->telephone }}</span>
    </div>
    @endif
    <div class="info-row">
      <span class="info-label">{{ __('emails.pending_admin.offer') }}</span>
      <span class="info-value">{{ $candidature->offre->titre }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">{{ __('emails.pending_admin.company') }}</span>
      <span class="info-value">{{ $candidature->offre->entreprise->nom }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">{{ __('emails.pending_admin.date') }}</span>
      <span class="info-value">{{ $candidature->created_at->format('d/m/Y à H:i') }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">{{ __('emails.pending_admin.status') }}</span>
      <span class="info-value"><span class="status-badge status-badge--warning">{{ __('emails.pending_admin.status_pending') }}</span></span>
    </div>
  </div>

  @if($candidature->message)
  <div class="info-card">
    <div class="info-card-title">{{ __('emails.pending_admin.candidate_message') }}</div>
    <p class="email-text" style="margin: 0;">{{ $candidature->message }}</p>
  </div>
  @endif

  <p class="email-text">
    {{ __('emails.pending_admin.cv_attached') }}
  </p>

  <div class="email-cta">
    <a href="{{ config('app.frontend_url') }}/admin/candidatures/{{ $candidature->id }}" class="btn-primary">
      {{ __('emails.pending_admin.review_application') }}
    </a>
  </div>

  <hr class="email-divider">

  <p class="email-text" style="font-size: 13px; color: #64748b;">
    <strong>{{ __('emails.pending_admin.possible_actions') }}</strong>
  </p>

  <ul class="email-list" style="font-size: 13px;">
    <li>{!! __('emails.pending_admin.action_validate') !!}</li>
    <li>{!! __('emails.pending_admin.action_reject') !!}</li>
  </ul>

  <p class="email-text" style="font-size: 13px; color: #64748b;">
    <strong>{{ __('emails.pending_admin.why_validation_title') }}</strong><br>
    {!! __('emails.pending_admin.why_validation_text', ['score' => $matchingScore]) !!}
  </p>
@endsection

@extends('emails.layout')

@section('header_badge', __('emails.validated_company.badge'))

@section('hero')
  <div class="email-hero-icon">
    <svg width="32" height="32" fill="white" viewBox="0 0 24 24">
      <path d="M9 11H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm2-7h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11z"/>
    </svg>
  </div>
  <div class="email-hero-title">{{ __('emails.validated_company.hero_title') }}</div>
  <div class="email-hero-subtitle">{{ __('emails.validated_company.hero_subtitle') }}</div>
@endsection

@section('content')
  <p class="email-greeting">{!! __('emails.validated_company.greeting', ['company' => $candidature->offre->entreprise->nom]) !!}</p>

  <p class="email-text">
    {!! __('emails.validated_company.intro', ['job_title' => $candidature->offre->titre]) !!}
  </p>

  <p class="email-text">
    {!! __('emails.validated_company.auto_validated') !!}
  </p>

  <!-- Score de matching -->
  <div class="highlight-box">
    <div class="highlight-box-label">{{ __('emails.validated_company.compatibility_score') }}</div>
    <div class="highlight-box-value">{{ $matchingScore }}%</div>
  </div>

  <!-- Informations du candidat -->
  <div class="info-card">
    <div class="info-card-title">{{ __('emails.validated_company.candidate_info') }}</div>
    <div class="info-row">
      <span class="info-label">{{ __('emails.validated_company.name') }}</span>
      <span class="info-value">{{ $candidature->talent->name }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">{{ __('emails.validated_company.email') }}</span>
      <span class="info-value">{{ $candidature->talent->email }}</span>
    </div>
    @if($candidature->talent->telephone)
    <div class="info-row">
      <span class="info-label">{{ __('emails.validated_company.phone') }}</span>
      <span class="info-value">{{ $candidature->talent->telephone }}</span>
    </div>
    @endif
    @if($candidature->talent->ville || $candidature->talent->pays)
    <div class="info-row">
      <span class="info-label">{{ __('emails.validated_company.location') }}</span>
      <span class="info-value">{{ implode(', ', array_filter([$candidature->talent->ville, $candidature->talent->pays])) }}</span>
    </div>
    @endif
    <div class="info-row">
      <span class="info-label">{{ __('emails.validated_company.application_date') }}</span>
      <span class="info-value">{{ $candidature->created_at->format('d/m/Y à H:i') }}</span>
    </div>
  </div>

  @if($candidature->message)
  <div class="info-card">
    <div class="info-card-title">{{ __('emails.validated_company.candidate_message') }}</div>
    <p class="email-text" style="margin: 0;">{{ $candidature->message }}</p>
  </div>
  @endif

  <p class="email-text">
    {{ __('emails.validated_company.cv_attached') }}
  </p>

  <div class="email-cta">
    <a href="{{ config('app.frontend_url') }}/entreprise/candidatures/{{ $candidature->id }}" class="btn-primary">
      {{ __('emails.validated_company.view_application') }}
    </a>
  </div>

  <hr class="email-divider">

  <p class="email-text" style="font-size: 13px; color: #64748b;">
    <strong>{{ __('emails.validated_company.why_validated_title') }}</strong><br>
    {!! __('emails.validated_company.why_validated_text', ['score' => $matchingScore]) !!}
  </p>
@endsection

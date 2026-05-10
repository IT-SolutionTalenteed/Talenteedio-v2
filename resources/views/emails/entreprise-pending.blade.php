@extends('emails.layout')

@section('title', __('emails.entreprise_pending.title'))
@section('header_badge', __('emails.entreprise_pending.badge'))

@section('hero')
  <div style="text-align:center;">
    <div class="email-hero-icon" style="margin:0 auto 14px; background:rgba(242,159,31,0.2); border-color:rgba(242,159,31,0.4);">
      <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#f29f1f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
      </svg>
    </div>
    <div class="email-hero-title">{{ __('emails.entreprise_pending.hero_title') }}</div>
    <div class="email-hero-subtitle">{{ __('emails.entreprise_pending.hero_subtitle') }}</div>
  </div>
@endsection

@section('content')
  <p class="email-greeting">{{ __('emails.entreprise_pending.greeting', ['name' => $user->name]) }}</p>

  <p class="email-text">
    {!! __('emails.entreprise_pending.intro') !!}
  </p>

  <div class="info-card" style="border-left-color: #f29f1f;">
    <div class="info-card-title" style="color:#b45309;">{{ __('emails.entreprise_pending.info_title') }}</div>
    <div class="info-row">
      <span class="info-label">{{ __('emails.entreprise_pending.info_account') }}</span>
      <span class="info-value">{{ $user->email }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">{{ __('emails.entreprise_pending.info_status') }}</span>
      <span class="info-value">
        <span class="status-badge status-badge--pending">{{ __('emails.entreprise_pending.info_status_value') }}</span>
      </span>
    </div>
  </div>

  <p class="email-text">
    {{ __('emails.entreprise_pending.next_steps') }}
  </p>

  <p class="email-text" style="font-weight: 600; color: #040a5d;">{{ __('emails.entreprise_pending.features_title') }}</p>
  <ul class="email-list">
    <li>{{ __('emails.entreprise_pending.feature_1') }}</li>
    <li>{{ __('emails.entreprise_pending.feature_2') }}</li>
    <li>{!! __('emails.entreprise_pending.feature_3') !!}</li>
    <li>{{ __('emails.entreprise_pending.feature_4') }}</li>
  </ul>

  <div class="highlight-box">
    <div class="highlight-box-label">{{ __('emails.entreprise_pending.delay_label') }}</div>
    <div class="highlight-box-value" style="font-size:16px; color:#b45309;">{{ __('emails.entreprise_pending.delay_value') }}</div>
  </div>

  <hr class="email-divider">
  <p class="email-text" style="font-size:13px; color:#94a3b8; text-align:center;">
    {{ __('emails.entreprise_pending.contact') }}
  </p>
@endsection

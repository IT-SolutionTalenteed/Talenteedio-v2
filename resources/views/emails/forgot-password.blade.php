@extends('emails.layout')

@section('title', __('emails.forgot_password.title'))
@section('header_badge', __('emails.forgot_password.badge'))

@section('hero')
  <div style="text-align:center;">
    <div class="email-hero-icon" style="margin:0 auto 14px; background:rgba(25,43,194,0.15); border-color:rgba(25,43,194,0.35);">
      <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#192bc2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
      </svg>
    </div>
    <div class="email-hero-title">{{ __('emails.forgot_password.hero_title') }}</div>
    <div class="email-hero-subtitle">{{ __('emails.forgot_password.hero_subtitle') }}</div>
  </div>
@endsection

@section('content')
  <p class="email-greeting">{{ __('emails.forgot_password.greeting', ['name' => $user->name]) }}</p>

  <p class="email-text">
    {!! __('emails.forgot_password.intro') !!}
  </p>

  <div class="email-cta">
    <a href="{{ $resetUrl }}" class="btn-primary">
      {{ __('emails.forgot_password.cta') }}
    </a>
  </div>

  <div class="info-card" style="border-left-color: #192bc2;">
    <div class="info-card-title" style="color:#040a5d;">{{ __('emails.forgot_password.info_title') }}</div>
    <div class="info-row">
      <span class="info-label">{{ __('emails.forgot_password.info_account') }}</span>
      <span class="info-value">{{ $user->email }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">{{ __('emails.forgot_password.info_validity') }}</span>
      <span class="info-value">{{ __('emails.forgot_password.info_validity_value') }}</span>
    </div>
  </div>

  <p class="email-text">
    {{ __('emails.forgot_password.ignore') }}
  </p>

  <p class="email-text" style="font-size:12px; color:#94a3b8; word-break:break-all;">
    {{ __('emails.forgot_password.manual_link') }}<br>
    <a href="{{ $resetUrl }}" style="color:#192bc2;">{{ $resetUrl }}</a>
  </p>

  <hr class="email-divider">
  <p class="email-text" style="font-size:13px; color:#94a3b8; text-align:center;">
    {{ __('emails.forgot_password.contact') }}
  </p>
@endsection

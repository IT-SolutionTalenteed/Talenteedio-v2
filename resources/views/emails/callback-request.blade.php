@extends('emails.layout')

@section('title', __('emails.callback_request.title'))

@section('content')
<div style="padding: 32px 32px 0;">
  <div style="display:inline-block; background:#fff7ed; border-radius:12px; padding:10px 16px; margin-bottom:20px;">
    <span style="font-size:22px;">📞</span>
    <span style="color:#ea580c; font-weight:700; font-size:14px; vertical-align:middle; margin-left:6px;">{{ __('emails.callback_request.badge') }}</span>
  </div>
  <h1 style="font-size:22px; font-weight:700; color:#0f172a; margin-bottom:8px;">{{ __('emails.callback_request.hero_title') }}</h1>
  <p style="color:#64748b; font-size:15px;">{{ __('emails.callback_request.hero_subtitle') }}</p>
</div>

<div style="padding: 24px 32px;">
  <table style="width:100%; border-collapse:collapse;">
    <tr>
      <td style="padding:12px 16px; background:#f8fafc; border-radius:8px 8px 0 0; border-bottom:1px solid #e2e8f0;">
        <span style="font-size:12px; color:#94a3b8; text-transform:uppercase; letter-spacing:.05em;">{{ __('emails.callback_request.name') }}</span><br>
        <strong style="color:#0f172a; font-size:15px;">{{ $name }}</strong>
      </td>
    </tr>
    <tr>
      <td style="padding:12px 16px; background:#f8fafc; border-bottom:1px solid #e2e8f0;">
        <span style="font-size:12px; color:#94a3b8; text-transform:uppercase; letter-spacing:.05em;">{{ __('emails.callback_request.email') }}</span><br>
        <a href="mailto:{{ $email }}" style="color:#00235a; font-size:15px; font-weight:600;">{{ $email }}</a>
      </td>
    </tr>
    <tr>
      <td style="padding:12px 16px; background:#f8fafc; border-bottom:1px solid #e2e8f0;">
        <span style="font-size:12px; color:#94a3b8; text-transform:uppercase; letter-spacing:.05em;">{{ __('emails.callback_request.phone') }}</span><br>
        <a href="tel:{{ $phone }}" style="color:#00235a; font-size:15px; font-weight:600;">{{ $phone }}</a>
      </td>
    </tr>
    @if($userMessage)
    <tr>
      <td style="padding:12px 16px; background:#f8fafc; border-radius:0 0 8px 8px;">
        <span style="font-size:12px; color:#94a3b8; text-transform:uppercase; letter-spacing:.05em;">{{ __('emails.callback_request.message') }}</span><br>
        <p style="color:#334155; font-size:15px; margin-top:4px; white-space:pre-line;">{{ $userMessage }}</p>
      </td>
    </tr>
    @endif
  </table>

  <div style="margin-top:24px; padding:16px; background:#fff7ed; border-left:4px solid #f97316; border-radius:0 8px 8px 0;">
    <p style="color:#9a3412; font-size:14px; margin:0;">
      <strong>Action requise :</strong> Rappeler ce contact dans les meilleurs délais.
    </p>
  </div>
</div>
@endsection

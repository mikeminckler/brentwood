@component('mail::message')
# Email Verification

Hello {{ $user->name }},

Please click the button below to verify your email address.

@component('mail::button', ['url' => $user->getEmailVerificationUrl()])
Verify Email
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent

@component('mail::message')
# Reset Password

Hello {{ $user->name }},

Please click the button below to reset your password.

@component('mail::button', ['url' => $user->getResetPasswordUrl()])
Reset Password
@endcomponent

If you need any further assistance please contact helpdesk@brentwood.bc.ca

{{ config('app.name') }}
@endcomponent

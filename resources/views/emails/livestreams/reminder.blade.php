@component('mail::message')
# {{ $livestream->name }} Reminder

Hello {{ $user->name }},

This is a reminder that you have registered for the upcoming **{{ $livestream->name }}** on **{{ $livestream->date }}**.

@component('mail::button', ['url' => $url])
Join the {{ $livestream->name }}
@endcomponent

If you have any questions please contact us at [info@brentwood.ca](mailto:info@brentwood.ca).

Thank you for your interest in Brentwood College School and we look forward to speaking with you soon.
@endcomponent

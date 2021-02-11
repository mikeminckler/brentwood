@component('mail::message')
# Inquiry Confirmation

@if ($inquiry->target_grade && $inquiry->student_type && $inquiry->target_year)
Thank you for taking the time to contact us regarding a **Grade {{ $inquiry->target_grade }} {{ $inquiry->student_type }} student** starting in **{{ $inquiry->target_year.'-'.($inquiry->target_year + 1) }}**.
@else
Thank you for taking the time to fill in the form.
@endif

@if ($inquiry->livestreams->count())
@foreach ($inquiry->livestreams as $livestream)
You have registered for the **{{ $livestream->name }}** on **{{ $livestream->date }}**.

@component('mail::button', ['url' => $livestream->pivot->url])
Join the {{ $livestream->name }}
@endcomponent
@endforeach

We will send you a reminder email closer to the date if you lose track of this email.
@endif

@if ($inquiry->filtered_tags->count())
At any time you can return to your personalized webpage where you can find helpful links.

@component('mail::button', ['url' => $inquiry->url])
View Personalized Webpage
@endcomponent
@endif

If you have any questions please contact us at [info@brentwood.ca](mailto:info@brentwood.ca).

Thank you for your interest in Brentwood College School. We look forward to speaking with you soon.
@endcomponent

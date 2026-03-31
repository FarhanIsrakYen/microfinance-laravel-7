@component('mail::panel')
    Hello <strong>{{ $name }}</strong>,
    <p>{{ $body }}</p>

    Regards,
    {{ config('app.name') }}
@endcomponent

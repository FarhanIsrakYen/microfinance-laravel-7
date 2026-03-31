<br>
<div style="margin-right: 10px; margin-left: 10px; margin-top: 10px; margin-bottom: 10px;">
    <strong>Hello!</strong><br>
    <br>
    Please click the button below to verify your email address.

    @component('mail::button', ['url' => $url, 'color' => 'success'])
        Verify Now
    @endcomponent

    Regards,<br>
    {{ config('app.name') }}
    <br><br>
    <hr>

    If you'er having trobule clicking the "Verify Now" button, copy and paste the URL below into your web browser: {{ $url }}
</div>
<br>

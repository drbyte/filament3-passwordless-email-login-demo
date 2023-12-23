<x-mail::message>
# Login Access to Member Voting Site

You requested to access to your {{ config('app.name') }} account using **{{ $email }}**.

**You can use this link only once, and it expires after {{ $expiry }} minutes.**

<x-mail::button :url="$signedUrl">
    Confirm and Log In
</x-mail::button>

In case you did not request this login email, you can ignore it.

[If the button above is not visible, simply click this link instead]({{ $signedUrl }}).

If the link has expired, you can request another one by clicking [here]({{ $urlToTryAgain }}) .

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>

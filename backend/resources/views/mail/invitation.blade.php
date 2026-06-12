<x-mail::message>
# Einladung zur Familie {{ $familyName }}

Du wurdest eingeladen, der Familie **{{ $familyName }}** auf Family Board beizutreten.

<x-mail::button :url="$url">
Jetzt registrieren
</x-mail::button>

Falls der Button nicht funktioniert, kopiere diesen Link in den Browser:
{{ $url }}

Viele Grüße,<br>
{{ config('app.name') }}
</x-mail::message>

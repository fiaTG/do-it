<?php

namespace App\Support;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Holt eine iCal-Datei von einer nutzergegebenen URL. Weil hier der SERVER
 * fremde Adressen abruft, ist das ein klassischer SSRF-Vektor – deshalb:
 * nur http(s)/webcal auf Standard-Ports, ALLE aufgelösten IPs müssen öffentlich
 * sein (keine privaten/reservierten Bereiche, kein 127.x, kein 169.254.x),
 * Redirects werden manuell verfolgt und jede Station erneut geprüft.
 * Restrisiko DNS-Rebinding ist in ADR-0023 dokumentiert.
 */
class IcsFetcher
{
    public const MAX_BYTES = 2 * 1024 * 1024; // 2 MB

    private const MAX_REDIRECTS = 3;

    private const TIMEOUT_SECONDS = 6;

    public function fetch(string $url): string
    {
        for ($hop = 0; $hop <= self::MAX_REDIRECTS; $hop++) {
            $url = $this->assertPublicUrl($url);
            $response = $this->get($url);

            if ($response->redirect()) {
                $location = (string) $response->header('Location');
                throw_if($location === '', new IcsFetchException(
                    'Der Kalender ist unter dieser Adresse nicht erreichbar.',
                ));
                $url = $this->resolveRedirect($url, $location);

                continue;
            }

            throw_unless($response->successful(), new IcsFetchException(
                'Der Kalender ist unter dieser Adresse nicht erreichbar (HTTP '.$response->status().').',
            ));

            $body = $response->body();
            throw_if(strlen($body) > self::MAX_BYTES, new IcsFetchException(
                'Die Kalenderdatei ist zu groß (max. 2 MB).',
            ));
            throw_unless(str_contains($body, 'BEGIN:VCALENDAR'), new IcsFetchException(
                'Unter dieser Adresse liegt kein iCal-Kalender (.ics).',
            ));

            return $body;
        }

        throw new IcsFetchException('Zu viele Weiterleitungen – bitte die direkte .ics-Adresse angeben.');
    }

    private function get(string $url): Response
    {
        try {
            return Http::timeout(self::TIMEOUT_SECONDS)
                ->withOptions(['allow_redirects' => false])
                ->withHeaders(['Accept' => 'text/calendar, */*'])
                ->get($url);
        } catch (ConnectionException) {
            throw new IcsFetchException('Der Kalender ist unter dieser Adresse nicht erreichbar.');
        }
    }

    /** Prüft Schema, Port und Ziel-IPs; liefert die ggf. normalisierte URL zurück. */
    private function assertPublicUrl(string $url): string
    {
        $url = trim($url);

        // Viele Anbieter geben Abo-Adressen als webcal:// aus – dahinter steckt HTTPS.
        if (str_starts_with(strtolower($url), 'webcal://')) {
            $url = 'https://'.substr($url, 9);
        }

        $parts = parse_url($url);
        $scheme = strtolower($parts['scheme'] ?? '');
        $host = $parts['host'] ?? '';

        throw_unless(
            in_array($scheme, ['http', 'https'], true) && $host !== '',
            new IcsFetchException('Bitte eine gültige http(s)- oder webcal-Adresse angeben.'),
        );
        throw_unless(
            in_array($parts['port'] ?? null, [null, 80, 443], true),
            new IcsFetchException('Kalender-Adressen mit eigenem Port werden nicht unterstützt.'),
        );

        foreach ($this->resolve($host) as $ip) {
            throw_if(
                filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false,
                new IcsFetchException('Diese Adresse zeigt auf ein internes Netz und kann nicht abonniert werden.'),
            );
        }

        return $url;
    }

    /** @return list<string> alle IPs, unter denen der Host erreichbar wäre */
    private function resolve(string $host): array
    {
        // IP-Literal (auch [IPv6]) direkt prüfen, keine DNS-Auflösung nötig.
        $literal = trim($host, '[]');
        if (filter_var($literal, FILTER_VALIDATE_IP) !== false) {
            return [$literal];
        }

        $ips = [];
        foreach (@dns_get_record($host, DNS_A | DNS_AAAA) ?: [] as $record) {
            $ips[] = $record['ip'] ?? $record['ipv6'] ?? null;
        }
        $ips = array_values(array_filter($ips));

        throw_if($ips === [], new IcsFetchException(
            'Die Adresse konnte nicht aufgelöst werden – bitte prüfen.',
        ));

        return $ips;
    }

    private function resolveRedirect(string $current, string $location): string
    {
        if (preg_match('#^https?://#i', $location) === 1) {
            return $location;
        }

        $parts = parse_url($current);
        $base = ($parts['scheme'] ?? 'https').'://'.($parts['host'] ?? '');

        return str_starts_with($location, '/')
            ? $base.$location
            : $base.rtrim(dirname($parts['path'] ?? '/'), '/').'/'.$location;
    }
}

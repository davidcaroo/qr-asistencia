<?php

declare(strict_types=1);

namespace App\Services;

use DateInterval;
use DateTimeImmutable;

final class QrTokenService
{
    public function __construct(
        private readonly string $secret,
        private readonly int $windowSeconds,
        private readonly string $baseUrl,
    ) {
    }

    public function issueToken(?DateTimeImmutable $now = null): array
    {
        $now = $now ?? new DateTimeImmutable('now');
        $windowStart = $now->setTimestamp((int) (floor($now->getTimestamp() / $this->windowSeconds) * $this->windowSeconds));
        $windowEnd = $windowStart->add(new DateInterval('PT' . $this->windowSeconds . 'S'));

        $payload = $windowStart->format('U') . ':' . $this->windowSeconds;
        $token = rtrim(strtr(base64_encode($payload . ':' . hash_hmac('sha256', $payload, $this->secret)), '+/', '-_'), '=');

        return [
            'token' => $token,
            'hash' => hash('sha256', $token),
            'window_start' => $windowStart,
            'window_end' => $windowEnd,
            'expires_at' => $windowEnd,
            'url' => rtrim($this->baseUrl, '/') . '/marcar?token=' . urlencode($token),
        ];
    }

    public function validate(string $token, ?DateTimeImmutable $now = null): bool
    {
        $now = $now ?? new DateTimeImmutable('now');
        $base64 = strtr($token, '-_', '+/');
        $padding = strlen($base64) % 4;
        if ($padding > 0) {
            $base64 .= str_repeat('=', 4 - $padding);
        }

        $decoded = base64_decode($base64, true);

        if ($decoded === false) {
            return false;
        }

        $parts = explode(':', $decoded);
        if (count($parts) !== 3) {
            return false;
        }

        [$issuedAt, $seconds, $signature] = $parts;
        if (!ctype_digit($issuedAt) || !ctype_digit($seconds)) {
            return false;
        }

        $payload = $issuedAt . ':' . $seconds;
        $expected = hash_hmac('sha256', $payload, $this->secret);

        if (!hash_equals($expected, $signature)) {
            return false;
        }

        $windowSeconds = (int) $seconds;
        $windowStart = (new DateTimeImmutable())->setTimestamp(((int) $issuedAt));
        $windowEnd = $windowStart->add(new DateInterval('PT' . $windowSeconds . 'S'));

        return $now >= $windowStart && $now <= $windowEnd;
    }
}
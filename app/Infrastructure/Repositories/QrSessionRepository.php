<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Core\Database;
use DateTimeImmutable;
use PDO;

final class QrSessionRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connection();
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO qr_sessions (token_hash, window_start, window_end, expires_at, active)
            VALUES (:token_hash, :window_start, :window_end, :expires_at, 1)
        ');
        $stmt->execute($data);

        return (int) $this->pdo->lastInsertId();
    }

    public function findValidByTokenHash(string $tokenHash, ?DateTimeImmutable $now = null): ?array
    {
        $now = $now ?? new DateTimeImmutable('now');

        $stmt = $this->pdo->prepare('
            SELECT *
            FROM qr_sessions
            WHERE token_hash = :token_hash
              AND active = 1
              AND revoked_at IS NULL
              AND expires_at >= :now
            ORDER BY id DESC
            LIMIT 1
        ');
        $stmt->execute([
            'token_hash' => $tokenHash,
            'now' => $now->format('Y-m-d H:i:s'),
        ]);

        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function revokeExpired(?DateTimeImmutable $now = null): int
    {
        $now = $now ?? new DateTimeImmutable('now');

        $stmt = $this->pdo->prepare('
            UPDATE qr_sessions
            SET active = 0, revoked_at = :now
            WHERE active = 1 AND expires_at < :now
        ');
        $stmt->execute(['now' => $now->format('Y-m-d H:i:s')]);

        return $stmt->rowCount();
    }

    public function latestActive(): ?array
    {
        $stmt = $this->pdo->query('SELECT * FROM qr_sessions WHERE active = 1 ORDER BY id DESC LIMIT 1');
        $row = $stmt->fetch();

        return $row ?: null;
    }
}

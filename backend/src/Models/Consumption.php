<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class Consumption
{
    public function __construct(private readonly PDO $pdo) {}

    /** @return array<int, array<string, mixed>> */
    public function recent(int $limit = 20): array
    {
        $statement = $this->pdo->prepare(
            'SELECT c.id, c.developer_name, d.name AS drink_name, d.caffeine_mg, c.consumed_at
             FROM consumptions c
             INNER JOIN drinks d ON d.id = c.drink_id
             ORDER BY c.consumed_at DESC, c.id DESC
             LIMIT :limit'
        );
        $statement->bindValue(':limit', max(1, min($limit, 20)), PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    }

    public function dailyCaffeineForDeveloper(string $developerName): int
    {
        $statement = $this->pdo->prepare(
            'SELECT COALESCE(SUM(d.caffeine_mg), 0)
             FROM consumptions c
             INNER JOIN drinks d ON d.id = c.drink_id
             WHERE c.developer_name = :developer_name
               AND c.consumed_at >= CURDATE()
               AND c.consumed_at < DATE_ADD(CURDATE(), INTERVAL 1 DAY)'
        );
        $statement->execute(['developer_name' => $developerName]);
        return (int) $statement->fetchColumn();
    }

    /** @return array<string, mixed> */
    public function create(int $drinkId, string $developerName): array
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO consumptions (drink_id, developer_name) VALUES (:drink_id, :developer_name)'
        );
        $statement->execute(['drink_id' => $drinkId, 'developer_name' => $developerName]);

        return $this->findById((int) $this->pdo->lastInsertId()) ?? throw new \RuntimeException('La consommation créée est introuvable.');
    }

    /** @return array<string, mixed>|null */
    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT c.id, c.developer_name, d.name AS drink_name, d.caffeine_mg, c.consumed_at
             FROM consumptions c
             INNER JOIN drinks d ON d.id = c.drink_id
             WHERE c.id = :id'
        );
        $statement->execute(['id' => $id]);
        $consumption = $statement->fetch();
        return $consumption === false ? null : $consumption;
    }

    public function delete(int $id): bool
    {
        $statement = $this->pdo->prepare('DELETE FROM consumptions WHERE id = :id');
        $statement->execute(['id' => $id]);
        return $statement->rowCount() > 0;
    }
}

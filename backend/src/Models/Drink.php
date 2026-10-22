<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class Drink
{
    public function __construct(private readonly PDO $pdo) {}

    /** @return array<int, array<string, mixed>> */
    public function all(): array
    {
        $statement = $this->pdo->query('SELECT id, name, caffeine_mg, volume_ml FROM drinks ORDER BY name ASC, id ASC');
        return $statement->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT id, name, caffeine_mg, volume_ml FROM drinks WHERE id = :id');
        $statement->execute(['id' => $id]);
        $drink = $statement->fetch();
        return $drink === false ? null : $drink;
    }

    /** @return array<string, mixed> */
    public function create(string $name, int $caffeineMg, int $volumeMl): array
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO drinks (name, caffeine_mg, volume_ml) VALUES (:name, :caffeine_mg, :volume_ml)'
        );
        $statement->execute([
            'name' => $name,
            'caffeine_mg' => $caffeineMg,
            'volume_ml' => $volumeMl,
        ]);

        return $this->findById((int) $this->pdo->lastInsertId()) ?? throw new \RuntimeException('La boisson créée est introuvable.');
    }
}

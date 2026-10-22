<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Consumption;
use App\Models\Drink;
use App\Responses\ApiProblemException;
use Psr\Log\LoggerInterface;

final class CoffeeService
{
    public const DAILY_CAFFEINE_LIMIT_MG = 400;

    public function __construct(
        private readonly Drink $drinks,
        private readonly Consumption $consumptions,
        private readonly LoggerInterface $logger
    ) {}

    /** @return array<string, mixed> */
    public function recordConsumption(int $drinkId, string $developerName): array
    {
        $drink = $this->drinks->findById($drinkId);
        if ($drink === null) {
            throw new ApiProblemException(404, 'Drink Not Found', "Aucune boisson ne correspond à l'identifiant {$drinkId}.");
        }

        $alreadyConsumed = $this->consumptions->dailyCaffeineForDeveloper($developerName);
        $newTotal = $alreadyConsumed + (int) $drink['caffeine_mg'];

        if ($newTotal > self::DAILY_CAFFEINE_LIMIT_MG) {
            $detail = sprintf(
                'Seuil de caféine dépassé ! %s a déjà consommé %d mg aujourd’hui (limite : %d mg). Cette boisson porterait le total à %d mg.',
                $developerName,
                $alreadyConsumed,
                self::DAILY_CAFFEINE_LIMIT_MG,
                $newTotal
            );
            $this->logger->warning('Consommation refusée : limite quotidienne dépassée.', [
                'developer_name' => $developerName,
                'consumed_mg' => $alreadyConsumed,
                'attempted_drink_mg' => (int) $drink['caffeine_mg'],
            ]);
            throw new ApiProblemException(429, 'Caffeine Limit Exceeded', $detail);
        }

        return $this->consumptions->create($drinkId, $developerName);
    }
}

<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\UrlHelper;
use App\Models\Consumption;
use App\Responses\ApiProblemException;
use App\Services\CoffeeService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class ConsumptionController
{
    public function __construct(
        private readonly Consumption $consumptions,
        private readonly CoffeeService $coffeeService
    ) {}

    public function index(Request $request, Response $response): Response
    {
        $baseUrl = UrlHelper::getBaseUrl($request);
        $payload = array_map(fn (array $item): array => $this->serializeConsumption($item, $baseUrl), $this->consumptions->recent(20));
        return $this->json($response, $payload);
    }

    public function store(Request $request, Response $response): Response
    {
        $body = $request->getParsedBody();
        if (!is_array($body)) {
            throw new ApiProblemException(400, 'Invalid Request Body', 'Le corps de la requête doit être un objet JSON valide.');
        }

        $drinkId = filter_var($body['drinkId'] ?? null, FILTER_VALIDATE_INT);
        $developerName = trim(is_string($body['developerName'] ?? null) ? $body['developerName'] : '');

        if ($drinkId === false || $drinkId <= 0 || $developerName === '' || $this->characterLength($developerName) > 100) {
            throw new ApiProblemException(400, 'Missing or Invalid Fields', 'Les champs drinkId et developerName sont obligatoires. drinkId doit être un entier positif et developerName doit contenir entre 1 et 100 caractères.');
        }

        $consumption = $this->coffeeService->recordConsumption($drinkId, $developerName);
        $serialized = $this->serializeConsumption($consumption, UrlHelper::getBaseUrl($request));
        return $this->json($response, $serialized, 201);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $rawId = $args['id'] ?? null;
        if (!is_string($rawId) || !ctype_digit($rawId) || (int) $rawId <= 0) {
            throw new ApiProblemException(404, 'Consumption Not Found', 'La consommation demandée est introuvable.');
        }

        if (!$this->consumptions->delete((int) $rawId)) {
            throw new ApiProblemException(404, 'Consumption Not Found', "Aucune consommation ne correspond à l'identifiant {$rawId}.");
        }

        return $response->withStatus(204);
    }

    private function serializeConsumption(array $item, string $baseUrl): array
    {
        $id = (int) $item['id'];
        return [
            'id' => $id,
            'developerName' => (string) $item['developer_name'],
            'drinkName' => (string) $item['drink_name'],
            'caffeineMg' => (int) $item['caffeine_mg'],
            'consumedAt' => (string) $item['consumed_at'],
            '_links' => [
                'delete' => ['href' => $baseUrl . '/api/consumptions/' . $id, 'method' => 'DELETE'],
            ],
        ];
    }

    private function json(Response $response, array $data, int $status = 200): Response
    {
        $response->getBody()->write((string) json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response->withHeader('Content-Type', 'application/json; charset=utf-8')->withStatus($status);
    }
    private function characterLength(string $value): int
    {
        $length = preg_match_all('/./us', $value);
        return $length === false ? strlen($value) : $length;
    }

}

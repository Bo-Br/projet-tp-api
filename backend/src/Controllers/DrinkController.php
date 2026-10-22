<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\UrlHelper;
use App\Models\Drink;
use App\Responses\ApiProblemException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class DrinkController
{
    public function __construct(private readonly Drink $drinks) {}

    public function index(Request $request, Response $response): Response
    {
        $baseUrl = UrlHelper::getBaseUrl($request);
        $payload = array_map(fn (array $drink): array => $this->serializeDrink($drink, $baseUrl), $this->drinks->all());
        return $this->json($response, $payload);
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $id = $this->parseId($args['id'] ?? null);
        $drink = $this->drinks->findById($id);
        if ($drink === null) {
            throw new ApiProblemException(404, 'Drink Not Found', "Aucune boisson ne correspond à l'identifiant {$id}.");
        }
        return $this->json($response, $this->serializeDrink($drink, UrlHelper::getBaseUrl($request)));
    }

    public function store(Request $request, Response $response): Response
    {
        $body = $request->getParsedBody();
        if (!is_array($body)) {
            throw new ApiProblemException(400, 'Invalid Request Body', 'Le corps de la requête doit être un objet JSON valide.');
        }

        $name = trim(is_string($body['name'] ?? null) ? $body['name'] : '');
        $caffeineRaw = $body['caffeineMg'] ?? null;
        $volumeRaw = $body['volumeMl'] ?? null;

        if ($name === '' || $this->characterLength($name) > 100 || !is_numeric($caffeineRaw) || !is_numeric($volumeRaw)) {
            throw new ApiProblemException(400, 'Missing or Invalid Fields', 'Les champs name, caffeineMg et volumeMl sont obligatoires. name doit contenir au maximum 100 caractères, caffeineMg doit être un entier positif ou nul et volumeMl un entier strictement positif.');
        }

        $caffeineMg = filter_var($caffeineRaw, FILTER_VALIDATE_INT);
        $volumeMl = filter_var($volumeRaw, FILTER_VALIDATE_INT);
        if ($caffeineMg === false || $caffeineMg < 0 || $volumeMl === false || $volumeMl <= 0) {
            throw new ApiProblemException(400, 'Missing or Invalid Fields', 'caffeineMg doit être un entier positif ou nul et volumeMl un entier strictement positif.');
        }

        $drink = $this->drinks->create($name, $caffeineMg, $volumeMl);
        $baseUrl = UrlHelper::getBaseUrl($request);
        $serialized = $this->serializeDrink($drink, $baseUrl);

        return $this->json($response, $serialized, 201)
            ->withHeader('Location', $baseUrl . '/api/drinks/' . (int) $drink['id']);
    }

    private function serializeDrink(array $drink, string $baseUrl): array
    {
        $id = (int) $drink['id'];
        return [
            'id' => $id,
            'name' => (string) $drink['name'],
            'caffeineMg' => (int) $drink['caffeine_mg'],
            'volumeMl' => (int) $drink['volume_ml'],
            '_links' => [
                'self' => ['href' => $baseUrl . '/api/drinks/' . $id, 'method' => 'GET'],
            ],
        ];
    }

    private function parseId(mixed $value): int
    {
        if (!is_string($value) || !ctype_digit($value) || (int) $value <= 0) {
            throw new ApiProblemException(404, 'Resource Not Found', 'La ressource demandée est introuvable.');
        }
        return (int) $value;
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

<?php

declare(strict_types=1);

namespace App\Responses;

use Psr\Http\Message\ResponseInterface as Response;

final class ProblemDetailsResponse
{
    public static function create(
        Response $response,
        int $status,
        string $title,
        string $detail,
        string $type = 'about:blank'
    ): Response {
        $payload = [
            'type' => $type,
            'title' => $title,
            'status' => $status,
            'detail' => $detail,
        ];

        $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $response->getBody()->write($json === false ? '{}' : $json);

        return $response
            ->withHeader('Content-Type', 'application/problem+json; charset=utf-8')
            ->withStatus($status);
    }
}

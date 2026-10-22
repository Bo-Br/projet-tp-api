<?php

declare(strict_types=1);

namespace App\Middlewares;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

final class EcoMetricsMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        $startTime = microtime(true);
        $response = $handler->handle($request);
        $executionTimeMs = round((microtime(true) - $startTime) * 1000, 2);

        $payloadSizeBytes = $response->getBody()->getSize();
        if ($payloadSizeBytes === null) {
            $position = $response->getBody()->tell();
            $response->getBody()->rewind();
            $payload = $response->getBody()->getContents();
            $payloadSizeBytes = strlen($payload);
            $response->getBody()->seek($position);
        }
        $payloadSizeBytes = (int) $payloadSizeBytes;

        // Formule d'estimation imposée par le support du TP.
        $estimatedCo2Grams = ($payloadSizeBytes * 0.000000011) + ($executionTimeMs * 0.000001);

        return $response
            ->withHeader('X-Response-Time-Ms', (string) $executionTimeMs)
            ->withHeader('X-Payload-Size-Bytes', (string) $payloadSizeBytes)
            ->withHeader('X-Estimated-CO2-Grams', sprintf('%.8f', $estimatedCo2Grams));
    }
}

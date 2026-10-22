<?php

namespace App\Responses;

use Psr\Http\Message\ResponseInterface as Response;

class ProblemDetailsResponse{

  public static function create(
    Response $response,
    int $status,
    string $title,
    string $detail,
    string $type = "about:blank"
  ): Response{

    $payload = [
      "type" => $type,
      "title" => $title,
      "status" => $status,
      "detail" => $detail
    ];

    $response->getBody()->write(json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

    return $response->withHeader("Content-Type", "application/problem+json")->withStatus($status);
  }

}
<?php
namespace App\Controllers;

use App\Services\StationService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteContext;
use App\Core\UrlHelper;

class StationController
{

    public function __construct(
        private StationService $stationService
    ) {}

    public function index(Request $request, Response $response): Response
    {

        $stations = $this->stationService->getAllStations();

        $baseUrl = UrlHelper::getBaseUrl($request);

        $data = array_map(fn($s) => [
          "id" => $s->id,
          "location_name" => $s->locationName,
          "powerKw" => $s->powerKw,
          "isAvailable" => $s->isAvailable,
          "_links" => [
            "self" => [
              "href" => "{$baseUrl}/api/stations/{$s->id}",
              "method" => "GET"
              ]
          ]
        ], $stations);

        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_SLASHES));

        return $response->withHeader("Content-Type", "application/json")->withStatus(200);
    }

    public function storeSession(Request $request, Response $response): Response{

      $body = $request->getParsedBody() ?? json_decode($request->getBody()->getContents(), true);

      if(
        empty($body["stationId"]) 
        || empty($body["userName"])
        || empty($body["kwhRequested"])
        ){
        return ProblemDetailsResponse::create(
          response: $response, 
          status: 400, 
          title: "Validation Error", 
          description: "Les champs stationId, userName et kwhRequested sont requis."
          );
      }

      $station = $this->stationService->getStationById((int) $body["stationId"]);

      if(!$station){
        return ProblemDetailsResponse::create(
          response: $response,
          status:404,
          title: "Station Not Found",
          description: "La borne de recharge demandée n'existe pas."
        );
      }

      if((int) $body["kwhRequested"] > $station->powerKw){
        return ProblemDetailsResponse::create(
          response: $response,
          status: 429,
          title: "Power Exceeded",
          description: "La puissance demandée ({$body['kwhRequested']} kWh) dépasse la limite de la borne ({$station->powerKw} kW)."
        );
      }

      $session = $this->stationService->createChargeSession(
        stationId: (int) $body["stationId"],
        userName: $body["userName"],
        kwhRequested: (int) $body["kwhRequested"]
      );

      $baseUrl = UrlHelper::getBaseUrl($request);

      $payload = [
        "id" => $session->id,
        "stationId" => $session->stationId,
        "userName" => $session->userName,
        "kwhRequested" => $session->kwhRequested,
        "createdAt" => $session->createdAt,
        "_links" => [
          "station" => [
            "href" => "{$baseUrl}/api/stations/{$session->stationId}",
            "method" => "GET"
            ]
        ]
      ];

      $response->getBody()->write(json_encode($payload, JSON_UNESCAPED_SLASHES));

      return $response->withHeader("Content-Type", "application/json")->withStatus(201);

    }
}

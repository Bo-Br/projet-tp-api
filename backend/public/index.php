<?php

use App\Controllers\StationController;
use App\Core\Database;
use App\Middlewares\CorsMiddleware;
use App\Middlewares\EcoMetricsMiddleware;
use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Slim\Factory\AppFactory;

require_once "../vendor/autoload.php";


$dotenv = Dotenv::createImmutable("../");
$dotenv->load();

$containerBuilder = new ContainerBuilder();
$containerBuilder->useAutowiring(true);

$containerBuilder->addDefinitions([
  PDO::class => function(){
    return Database::getInstance();
  }
]);

$container = $containerBuilder->build();
AppFactory::setContainer($container);

$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->add(new EcoMetricsMiddleware());
$app->add(new CorsMiddleware);

$app->options("/{routes:.+}", function ($request, $response){
  return $response;
});

// Stations routes
$app->get("/api/stations", [StationController::class, "index"]);

// Sessions routes
$app->get("/api/sessions", [StationController::class, "getSessions"]);
$app->post("/api/sessions", [StationController::class, "storeSession"]);

/**
 * Mettre en place sur /api/sessions
 * Requête GET | DELETE | UPDATE
 */

$app->run();
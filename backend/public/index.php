<?php

declare(strict_types=1);

use App\Controllers\ConsumptionController;
use App\Controllers\DrinkController;
use App\Core\Database;
use App\Middlewares\CorsMiddleware;
use App\Middlewares\EcoMetricsMiddleware;
use App\Responses\ApiProblemException;
use App\Responses\ProblemDetailsResponse;
use DI\ContainerBuilder;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;
use Dotenv\Dotenv;
use Psr\Http\Message\ServerRequestInterface as Request;

$backendRoot = dirname(__DIR__);
require $backendRoot . '/vendor/autoload.php';

if (is_file($backendRoot . '/.env')) {
    Dotenv::createImmutable($backendRoot)->safeLoad();
}

date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Europe/Paris');

$logDirectory = $backendRoot . '/var/logs';
if (!is_dir($logDirectory)) {
    mkdir($logDirectory, 0775, true);
}
$logger = new \Monolog\Logger('devcoffee');
$logger->pushHandler(new \Monolog\Handler\StreamHandler($logDirectory . '/app.log', \Monolog\Logger::WARNING));

$containerBuilder = new ContainerBuilder();
$containerBuilder->useAutowiring(true);
$containerBuilder->addDefinitions([
    PDO::class => static fn (): PDO => Database::getInstance(),
    LoggerInterface::class => static function () use ($logger): LoggerInterface { return $logger; },
]);
$container = $containerBuilder->build();
AppFactory::setContainer($container);

$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

$errorMiddleware = $app->addErrorMiddleware(false, true, true);
$errorMiddleware->setDefaultErrorHandler(
    function (Request $request, Throwable $exception) use ($app, $logger) {
        $status = 500;
        $title = 'Internal Server Error';
        $detail = 'Une erreur interne est survenue. Consultez les journaux du serveur.';
        $type = 'about:blank';

        if ($exception instanceof ApiProblemException) {
            $status = $exception->status;
            $title = $exception->title;
            $detail = $exception->getMessage();
            $type = $exception->type;
            if ($status >= 500) {
                $logger->error($exception->getMessage(), ['exception' => $exception]);
            }
        } elseif ($exception instanceof HttpNotFoundException) {
            $status = 404;
            $title = 'Not Found';
            $detail = 'La ressource demandée est introuvable.';
        } elseif ($exception instanceof HttpMethodNotAllowedException) {
            $status = 405;
            $title = 'Method Not Allowed';
            $detail = 'Cette méthode HTTP n est pas autorisée pour cette ressource.';
        } else {
            $logger->error('Erreur API non gérée.', [
                'exception' => $exception,
                'method' => $request->getMethod(),
                'uri' => (string) $request->getUri(),
            ]);
        }

        $response = $app->getResponseFactory()->createResponse();
        return ProblemDetailsResponse::create($response, $status, $title, $detail, $type);
    }
);

// L’éco-métrique enveloppe CORS et les erreurs pour instrumenter toutes les réponses,
// tandis que CORS expose les en-têtes à Vue même pour les réponses RFC 7807.
$app->add(new CorsMiddleware());
$app->add(new EcoMetricsMiddleware());

$app->options('/{routes:.+}', static fn ($request, $response) => $response->withStatus(204));

$app->get('/api/drinks', [DrinkController::class, 'index']);
$app->get('/api/drinks/{id}', [DrinkController::class, 'show']);
$app->post('/api/drinks', [DrinkController::class, 'store']);

$app->get('/api/consumptions', [ConsumptionController::class, 'index']);
$app->post('/api/consumptions', [ConsumptionController::class, 'store']);
$app->delete('/api/consumptions/{id}', [ConsumptionController::class, 'delete']);

$app->run();

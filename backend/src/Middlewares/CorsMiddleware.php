<?php
namespace App\Middlewares;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class CorsMiddleware implements MiddlewareInterface
{

    public function process(
        Request $request,
        RequestHandler $handler
    ): Response {

        $response = $handler->handle($request);

        return $response
        ->withHeader("Access-Control-Allow-Origin", "*")
        ->withHeader("Access-Control-Allow-Headers", "X-Requested-With, Content-Type, Origin, Authorization")
        ->withHeader("Access-Control-Allow-Methods", "GET, POST, PUT, DELETE, OPTIONS")
        ->withHeader("Access-Control-Expose-Headers", "X-Response-Time-Ms, X-Payload-Size-Bytes, X-Estimated-CO2-Grams");
    }

}

<?php

namespace App\Core;

use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteContext;

class UrlHelper
{

    public static function getBaseUrl(Request $request): string
    {
        $uri       = $request->getUri();
        $scheme    = $uri->getScheme();
        $authority = $uri->getAuthority();

        $routeContext = RouteContext::fromRequest($request);
        $basePath     = $routeContext->getBasePath();

        $baseUrl = rtrim("{$scheme}://{$authority}{$basePath}", "/");

        return $baseUrl;
    }
}

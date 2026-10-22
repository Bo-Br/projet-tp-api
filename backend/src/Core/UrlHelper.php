<?php

declare(strict_types=1);

namespace App\Core;

use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteContext;

final class UrlHelper
{
    public static function getBaseUrl(Request $request): string
    {
        $uri = $request->getUri();
        $routeContext = RouteContext::fromRequest($request);
        $basePath = $routeContext->getBasePath();

        return rtrim(sprintf('%s://%s%s', $uri->getScheme() ?: 'http', $uri->getAuthority(), $basePath), '/');
    }
}

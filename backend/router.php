<?php
// Router du serveur PHP intégré : toutes les URLs API passent par Slim,
// tandis que les éventuels fichiers statiques restent servis directement.
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$staticFile = __DIR__ . '/public' . $path;
if ($path !== '/' && is_file($staticFile)) {
    return false;
}
require __DIR__ . '/public/index.php';

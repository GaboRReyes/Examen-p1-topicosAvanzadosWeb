<?php

require_once __DIR__ . '/controllers/urlController.php';

class Router
{
    public static function handle()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];

        $base = '/api_p1/Examen-p1-topicosAvanzadosWeb/public';
        $uri = str_replace($base, '', $uri);
        $uri = rtrim($uri, '/');

        if ($method === 'POST' && $uri === '/api/v1/urls') {
            UrlController::create();
            exit;

        } elseif ($method === 'GET' && preg_match('#^/api/v1/urls/([a-zA-Z0-9]+)/stats$#', $uri, $matches)) {
            UrlController::stats($matches[1]);
            exit;

        } elseif ($method === 'GET' && preg_match('#^/([a-zA-Z0-9]+)$#', $uri, $matches)) {
            UrlController::redirect($matches[1]);
            exit;

        } else {
            http_response_code(404);
            echo json_encode(["error" => "Route not found"]);
            exit;
        }
    }
}

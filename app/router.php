<?php

require_once __DIR__ . '/controllers/urlController.php';

class Router {

    public static function handle() {

        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'POST' && $uri === '/url-shortener/public/api/v1/urls') {
            UrlController::create();
        }

        elseif ($method === 'GET' && preg_match('#/api/v1/urls/([a-zA-Z0-9]+)/stats#', $uri, $matches)) {
            UrlController::stats($matches[1]);
        }

        elseif ($method === 'GET' && preg_match('#/url-shortener/public/([a-zA-Z0-9]+)$#', $uri, $matches)) {
            UrlController::redirect($matches[1]);
        }

        else {
            http_response_code(404);
            echo json_encode(["error" => "Route not found"]);
        }
    }
}

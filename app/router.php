<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$base = '/api_p1/Examen-p1-topicosAvanzadosWeb/public';
$uri = str_replace($base, '', $uri);

if ($method === 'POST' && $uri === '/api/v1/urls') {
    UrlController::create();
}

elseif ($method === 'GET' && preg_match('#^/api/v1/urls/([a-zA-Z0-9]+)/stats$#', $uri, $matches)) {
    UrlController::stats($matches[1]);
}

else {
    http_response_code(404);
    echo json_encode(["error" => "Route not found"]);
}

<?php

require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../services/urlServices.php';

class UrlController {

    public static function create() {

        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['original_url']) || 
            !UrlService::validateUrl($data['original_url'])) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid URL"]);
            exit;
        }

        $config = require __DIR__ . '/../config.php';
        $pdo = Database::getConnection();

        // Evitar colisiones
        do {
            $code = UrlService::generateCode($config['code_length']);
            $stmt = $pdo->prepare("SELECT id FROM urls WHERE short_code = ?");
            $stmt->execute([$code]);
        } while ($stmt->rowCount() > 0);

        $stmt = $pdo->prepare(
            "INSERT INTO urls 
            (short_code, original_url, expiration_date, max_uses, creator_ip)
            VALUES (?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $code,
            $data['original_url'],
            $data['expiration_date'] ?? null,
            $data['max_uses'] ?? null,
            $_SERVER['REMOTE_ADDR']
        ]);

        http_response_code(201);
        echo json_encode([
            "short_code" => $code,
            "short_url" => $config['base_url'] . "/" . $code
        ]);
    }

    public static function redirect($code) {

        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("SELECT * FROM urls WHERE short_code = ?");
        $stmt->execute([$code]);
        $url = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$url) {
            http_response_code(404);
            echo json_encode(["error" => "Not found"]);
            exit;
        }

        if ($url['expiration_date'] && 
            strtotime($url['expiration_date']) < time()) {
            http_response_code(410);
            echo json_encode(["error" => "URL expired"]);
            exit;
        }

        if ($url['max_uses'] && 
            $url['visit_count'] >= $url['max_uses']) {
            http_response_code(410);
            echo json_encode(["error" => "Max uses reached"]);
            exit;
        }

        // Registrar visita
        $pdo->prepare(
            "INSERT INTO visits (short_code, ip_address, user_agent)
             VALUES (?, ?, ?)"
        )->execute([
            $code,
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);

        $pdo->prepare(
            "UPDATE urls SET visit_count = visit_count + 1
             WHERE short_code = ?"
        )->execute([$code]);

        header("Location: " . $url['original_url'], true, 302);
        exit;
    }

    public static function stats($code) {

        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("SELECT * FROM urls WHERE short_code = ?");
        $stmt->execute([$code]);
        $url = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$url) {
            http_response_code(404);
            echo json_encode(["error" => "Not found"]);
            exit;
        }

        $stmt = $pdo->prepare(
            "SELECT DATE(visited_at) as date, COUNT(*) as visits
             FROM visits
             WHERE short_code = ?
             GROUP BY DATE(visited_at)"
        );
        $stmt->execute([$code]);
        $visits = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            "short_code" => $code,
            "original_url" => $url['original_url'],
            "total_visits" => $url['visit_count'],
            "visits_by_day" => $visits
        ]);
    }
}

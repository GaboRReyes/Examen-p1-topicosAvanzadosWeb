<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");

require_once __DIR__ . '/../app/router.php';

Router::handle();

header("Content-Type: application/json");

require_once __DIR__ . '/../app/router.php';

Router::handle();

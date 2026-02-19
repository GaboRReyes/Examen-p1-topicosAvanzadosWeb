<?php

header("Content-Type: application/json");

require_once __DIR__ . '/../app/Router.php';

Router::handle();

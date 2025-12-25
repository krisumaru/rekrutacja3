<?php

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

require __DIR__.'/../vendor/autoload.php';

if (!isset($_SERVER['APP_ENV'])) {
    (new Dotenv())->usePutenv(true)->bootEnv(__DIR__.'/../.env');
}

// Boot kernel to access the container
$kernel = new Kernel($_SERVER['APP_ENV'] ?? 'test', true);
$kernel->boot();

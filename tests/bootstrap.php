<?php

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

require __DIR__.'/../vendor/autoload.php';

set_error_handler(
    static function (int $severity, string $message, string $file, int $line): void {
        if (!(error_reporting() & $severity)) {
            return;
        }

        throw new ErrorException($message, 0, $severity, $file, $line);
    }
);

if (!isset($_SERVER['APP_ENV'])) {
    (new Dotenv())->usePutenv(true)->bootEnv(__DIR__.'/../.env');
}

// Boot kernel to access the container
$kernel = new Kernel($_SERVER['APP_ENV'] ?? 'test', true);
$kernel->boot();

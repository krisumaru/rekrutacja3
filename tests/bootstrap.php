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

$container = $kernel->getContainer();

// Prepare SQLite (or other) test database schema using DBAL
/** @var Doctrine\DBAL\Connection $conn */
$conn = $container->get('doctrine.dbal.default_connection');

// Use a schema that works across SQLite and Postgres (for tests we use SQLite)
$platform = $conn->getDatabasePlatform();

$conn->executeStatement('DROP TABLE IF EXISTS contact_messages');

if ($platform instanceof Doctrine\DBAL\Platforms\SQLitePlatform) {
    $conn->executeStatement(<<<'SQL'
CREATE TABLE contact_messages (
    id INTEGER PRIMARY KEY,
    full_name TEXT NOT NULL,
    email TEXT NOT NULL,
    message TEXT NOT NULL,
    consent INTEGER NOT NULL,
    created_at TEXT NOT NULL
)
SQL);
} else {
    // Fallback generic SQL (e.g., Postgres if tests run differently)
    $conn->executeStatement(<<<'SQL'
CREATE TABLE contact_messages (
    id SERIAL PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    consent BOOLEAN NOT NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL
)
SQL);
}

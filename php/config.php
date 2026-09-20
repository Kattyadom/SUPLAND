<?php
declare(strict_types=1);

// Ajusta estos valores a tu instalación de MySQL.
return [
    'host' => getenv('SUPLAND_DB_HOST') ?: '127.0.0.1',
    'port' => getenv('SUPLAND_DB_PORT') ?: '3306',
    'database' => getenv('SUPLAND_DB_NAME') ?: 'supland',
    'user' => getenv('SUPLAND_DB_USER') ?: 'root',
    'password' => getenv('SUPLAND_DB_PASSWORD') ?: '',
];

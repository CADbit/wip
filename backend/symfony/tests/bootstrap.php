<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (file_exists(dirname(__DIR__).'/.env.test')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env.test');
}

if (isset($_ENV['BOOTSTRAP_RESET_DATABASE']) && $_ENV['BOOTSTRAP_RESET_DATABASE']) {
    exec(sprintf(
        'php "%s/../bin/console" doctrine:database:drop --env=test --force --no-interaction',
        __DIR__
    ));
    exec(sprintf(
        'php "%s/../bin/console" doctrine:database:create --env=test --no-interaction',
        __DIR__
    ));
    exec(sprintf(
        'php "%s/../bin/console" doctrine:migrations:migrate --env=test --no-interaction',
        __DIR__
    ));
}

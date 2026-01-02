<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

// This file is the entry point to configure your own services.
// Files in the packages/ subdirectory configure your dependencies.
// See also https://symfony.com/doc/current/service_container/import.html

// Put parameters here that don't need to change on each machine where the app is deployed
// https://symfony.com/doc/current/best_practices.html#use-parameters-for-application-configuration

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->parameters();

    $services = $containerConfigurator->services()
        // default configuration for services in *this* file
        ->defaults()
            ->autowire()      // Automatically injects dependencies in your services.
            ->autoconfigure() // Automatically registers your services as commands, event subscribers, etc.
    ;

    // makes classes in src/ available to be used as services
    // this creates a service per class whose id is the fully-qualified class name
    $services->load('App\\', '../src/')
        ->exclude('../src/{DependencyInjection,Entity,Migrations,Tests,Kernel.php}');

    // add more service definitions when explicit configuration is needed
    // please note that last definitions always *replace* previous ones

    // Repository interface binding
    $services->alias(
        'App\Resource\Domain\Repository\ResourceRepositoryInterface',
        'App\Resource\Infrastructure\Doctrine\ResourceRepository'
    );

    $services->alias(
        'App\Reservation\Domain\Repository\ReservationRepositoryInterface',
        'App\Reservation\Infrastructure\Doctrine\ReservationRepository'
    );
};


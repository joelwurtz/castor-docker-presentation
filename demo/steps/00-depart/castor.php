<?php

namespace demo;

defined('CASTOR_USE_CHDIR') || define('CASTOR_USE_CHDIR', true);

use Castor\Attribute\AsListener;
use Castor\Docker\Event\RegisterServiceEvent;
use Castor\Docker\Service\PHPService;
use Castor\Docker\Service\PostgresService;

#[AsListener(RegisterServiceEvent::class)]
function register_services(RegisterServiceEvent $event): void
{
    $postgres = new PostgresService();
    $event->addService($postgres);

    $event->addService(
        (new PHPService('app'))
            ->withDirectory(__DIR__)
            ->withDomain('app.demo.test')
            ->link($postgres)
    );
}

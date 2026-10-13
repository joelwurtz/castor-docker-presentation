<?php

namespace demo;

defined('CASTOR_USE_CHDIR') || define('CASTOR_USE_CHDIR', true);

use Castor\Attribute\AsContext;
use Castor\Attribute\AsListener;
use Castor\Context;
use Castor\Docker\Event\RegisterServiceEvent;
use Castor\Docker\Service\PHPService;
use Castor\Docker\Service\PostgresService;
use Castor\Docker\Service\RedisService;

#[AsContext(default: true)]
function default_context(): Context
{
    return new Context([
        'root_domain' => 'demo.test',
        'build_parallel' => true,
    ]);
}

#[AsListener(RegisterServiceEvent::class)]
function register_services(RegisterServiceEvent $event): void
{
    $postgres = new PostgresService();
    $event->addService($postgres);
    $event->addService(new RedisService());

    $event->addService(
        (new PHPService('app'))
            ->withDirectory(__DIR__)
            ->withDomain('app.demo.test')
            ->link($postgres)
            ->addExtension('redis')
            ->withPhpIni(['memory_limit' => '1G'])
            ->withDockerfile(__DIR__ . '/Dockerfile')
    );
}

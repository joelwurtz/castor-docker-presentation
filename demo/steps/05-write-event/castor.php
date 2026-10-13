<?php

namespace demo;

defined('CASTOR_USE_CHDIR') || define('CASTOR_USE_CHDIR', true);

use Castor\Attribute\AsContext;
use Castor\Attribute\AsListener;
use Castor\Context;
use Castor\Docker\Attribute\AsDockerComposeBuilder;
use Castor\Docker\Event\DockerComposeWriteEvent;
use Castor\Docker\Event\RegisterServiceEvent;
use Castor\Docker\Service\Builder\ComposeBuilder;
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

#[AsDockerComposeBuilder]
function add_adminer(ComposeBuilder $builder, Context $context): void
{
    $builder
        ->service('adminer')
            ->image('adminer:5')
            ->withHttpRouting('adminer.' . $context['root_domain'], 8080)
            ->profile('default')
        ->end()
    ;
}

#[AsListener(DockerComposeWriteEvent::class)]
function limit_postgres_memory(DockerComposeWriteEvent $event): void
{
    $event->compose['services']['postgres']['deploy']['resources']['limits']['memory'] = '256m';
}

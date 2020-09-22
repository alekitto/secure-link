<?php

declare(strict_types=1);

use Kcs\SecureLink;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->defaults()
        ->private()

        ->alias(SecureLink\GeneratorInterface::class, SecureLink\Generator::class)
        ->set(SecureLink\Generator::class)
            ->args([ service(SecureLink\Encoder\EncoderInterface::class) ])

        ->set(SecureLink\Encoder\EncoderRegistry::class)
            ->args([ tagged_iterator('kcs.secure_link.encoder') ])

        ->set('kcs.secure_link.encoder.plaintext', SecureLink\Encoder\PlaintextEncoder::class)
            ->tag('kcs.secure_link.encoder')

        ->set(SecureLink\Routing\Router::class)
            ->decorate('router', 'kcs.secure_link.router.inner')
            ->args([
                service('kcs.secure_link.router.inner'),
                service(SecureLink\GeneratorInterface::class),
            ])

        ->alias(SecureLink\RequestHandlerInterface::class, SecureLink\Handler::class)
        ->alias(SecureLink\HandlerInterface::class, SecureLink\Handler::class)
        ->set(SecureLink\Handler::class)
            ->args([
                tagged_iterator('kcs.secure_link.handler'),
                service(SecureLink\Encoder\EncoderInterface::class),
            ]);
};

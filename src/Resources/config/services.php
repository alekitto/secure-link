<?php

declare(strict_types=1);

use Kcs\SecureLink;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->defaults()
        ->private()

        ->set(SecureLink\Generator::class)
            ->args([ new Reference(SecureLink\Encoder\EncoderInterface::class) ])

        ->set(SecureLink\Encoder\EncoderRegistry::class)
            ->args([ tagged_iterator('kcs.secure_link.encoder') ])

        ->set('kcs.secure_link.encoder.plaintext', SecureLink\Encoder\PlaintextEncoder::class)
            ->tag('kcs.secure_link.encoder')

        ->alias(SecureLink\RequestHandlerInterface::class, SecureLink\Handler::class)
        ->alias(SecureLink\HandlerInterface::class, SecureLink\Handler::class)
        ->set(SecureLink\Handler::class)
            ->args([
                tagged_iterator('kcs.secure_link.handler'),
                new Reference(SecureLink\Encoder\EncoderInterface::class),
            ]);
};

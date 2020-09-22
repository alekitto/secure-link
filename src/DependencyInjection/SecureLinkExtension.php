<?php

declare(strict_types=1);

namespace Kcs\SecureLink\DependencyInjection;

use Aws\Kms\KmsClient;
use Kcs\SecureLink\Encoder\AwsKmsEncoder;
use Kcs\SecureLink\Encoder\EncoderInterface;
use Kcs\SecureLink\Encoder\EncoderRegistry;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use function assert;
use function class_exists;

class SecureLinkExtension extends Extension
{
    /**
     * @inheritDoc
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = $this->getConfiguration($configs, $container);
        assert($configuration !== null);

        $config = $this->processConfiguration($configuration, $configs);
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        if (! $config['enabled']) {
            return;
        }

        $loader->load('services.php');

        if (class_exists(KmsClient::class)) {
            $container
                ->register('.kcs.secure_link.kms_client', KmsClient::class)
                ->addArgument($config['aws_kms_options']);

            $container
                ->register('kcs.secure_link.encoder.aws_kms', AwsKmsEncoder::class)
                ->addArgument(new Reference('.kcs.secure_link.kms_client'))
                ->addArgument($config['dsn'])
                ->addTag('kcs.secure_link.encoder');
        }

        $container->register(EncoderInterface::class)
            ->setFactory([EncoderRegistry::class, 'getEncoder'])
            ->addArgument($config['dsn']);
    }
}

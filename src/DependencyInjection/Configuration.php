<?php

declare(strict_types=1);

namespace Kcs\SecureLink\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use function is_array;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('secure_link');

        // @phpstan-ignore-next-line
        $treeBuilder->getRootNode()
            ->addDefaultsIfNotSet()
            ->canBeDisabled()
            ->fixXmlConfig('aws_kms_option')
            ->children()
                ->scalarNode('dsn')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->example('plaintext://')
                ->end()
                ->variableNode('aws_kms_options')
                    ->validate()
                        ->ifTrue(static fn ($v) => ! is_array($v))
                        ->thenInvalid('AWS KMS options must be an array')
                    ->end()
                    ->defaultValue([])
                ->end()
            ->end();

        return $treeBuilder;
    }
}

<?php

declare(strict_types=1);

namespace Kcs\SecureLink\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

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
                ->arrayNode('aws_kms_options')
                    ->addDefaultsIfNotSet()
                    ->ignoreExtraKeys()
                    ->children()
                        ->scalarNode('region')->defaultValue('us-west-1')->end()
                        ->scalarNode('version')->defaultValue('2014-11-01')->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}

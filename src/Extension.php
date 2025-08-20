<?php

namespace AllManager\RestBehatExtension;

use AllManager\RestBehatExtension\Rest\ApiBrowser;
use Behat\Testwork\ServiceContainer\Extension as ExtensionInterface;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class Extension implements ExtensionInterface
{
    public function load(ContainerBuilder $container, array $config): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/Resources/services'));
        $loader->load('services.yaml');

        $container->findDefinition(ApiBrowser::class)->setBindings(['$host' => $config['host']]);
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
        $builder
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('host')->defaultValue('http://web')->end()
            ->end()
        ;
    }

    public function getConfigKey(): string
    {
        return 'api';
    }

    public function process(ContainerBuilder $container): void
    {
    }

    public function initialize(ExtensionManager $extensionManager): void
    {
    }
}

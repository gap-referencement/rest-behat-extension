<?php

namespace AllManager\RestBehatExtension;

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
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
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

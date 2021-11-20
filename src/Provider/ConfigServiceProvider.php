<?php

declare(strict_types=1);

/*
 * This file is part of DivineNii opensource projects.
 *
 * PHP version 7.4 and above required
 *
 * @author    Divine Niiquaye Ibok <divineibok@gmail.com>
 * @copyright 2019 DivineNii (https://divinenii.com/)
 * @license   https://opensource.org/licenses/BSD-3-Clause License
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rade\Provider;

use Rade\AppBuilder;
use Rade\DI\AbstractContainer;
use Rade\DI\Definition;
use Rade\DI\Definitions\Statement;
use Rade\DI\Loader\{ClosureLoader, DirectoryLoader, GlobFileLoader, PhpFileLoader, YamlFileLoader};
use Rade\DI\Services\{AliasedInterface, ServiceProviderInterface};
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderResolver;

/**
 * Symfony's Config component extension.
 *
 * @author Divine Niiquaye Ibok <divineibok@gmail.com>
 */
class ConfigServiceProvider implements AliasedInterface, ConfigurationInterface, ServiceProviderInterface
{
    private string $rootDir;

    public function __construct(string $rootDir)
    {
        $this->rootDir = \rtrim($rootDir, \DIRECTORY_SEPARATOR);
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias(): string
    {
        return 'config';
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder($this->getAlias());

        $treeBuilder->getRootNode()
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('locale')->defaultValue('en')->end()
                ->booleanNode('debug')->defaultFalse()->end()
                ->arrayNode('paths')
                    ->prototype('scalar')->isRequired()->cannotBeEmpty()->end()
                ->end()
                ->arrayNode('loaders')
                    ->prototype('scalar')->defaultValue([])->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function register(AbstractContainer $container, array $configs = []): void
    {
        // The default configs ...
        $container->parameters['locale'] = $configs['locale'] ?? null;
        $container->parameters['debug'] = $configs['debug'] ?? false;
        $container->parameters['project_dir'] = $this->rootDir;

        // Configurations ...
        $configLoaders = [...$configs['loaders'], ...[PhpFileLoader::class, YamlFileLoader::class, ClosureLoader::class, DirectoryLoader::class, GlobFileLoader::class]];

        if ($container instanceof AppBuilder) {
            $builderResolver = new LoaderResolver();
            $fileLocator = new FileLocator(\array_map([$container, 'parameter'], $configs['paths']));

            foreach ($configLoaders as $builderLoader) {
                $builderResolver->addLoader(new $builderLoader($container, $fileLocator));
            }

            $container->set('builder.loader_resolver', $builderResolver);
        }

        foreach ($configLoaders as &$configLoader) {
            $configLoader = new Statement($configLoader);
        }

        $container->autowire('config.loader_locator', new Definition(FileLocator::class, [\array_map([$container, 'parameter'], $configs['paths'])]));
        $container->set('config.loader_resolver', new Definition(LoaderResolver::class, [$configLoaders]));
    }
}

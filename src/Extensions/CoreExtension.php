<?php

declare(strict_types=1);

/*
 * This file is part of Biurad opensource projects.
 *
 * @copyright 2019 Biurad Group (https://biurad.com/)
 * @license   https://opensource.org/licenses/BSD-3-Clause License
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flange\Extensions;

use Rade\DI\Container;
use Rade\DI\Definition;
use Rade\DI\Extensions\AliasedInterface;
use Rade\DI\Extensions\DependenciesInterface;
use Rade\DI\Extensions\ExtensionInterface;
use Rade\Handler\EventHandler;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Console\Event\ConsoleEvent;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Rade Core Extension.
 *
 * @author Divine Niiquaye Ibok <divineibok@gmail.com>
 */
class CoreExtension implements AliasedInterface, ConfigurationInterface, DependenciesInterface, ExtensionInterface
{
    private string $rootDir;

    public function __construct(string $rootDir)
    {
        $this->rootDir = \rtrim(\realpath($rootDir), '\\/');
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias(): string
    {
        return 'core';
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(__CLASS__);

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('events_dispatcher')->end()
            ->end()
        ;

        return $treeBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function dependencies(): array
    {
        $dependencies = [[ConfigExtension::class, [$this->rootDir]], RoutingExtension::class];

        if (\class_exists(ConsoleEvent::class)) {
            $dependencies[] = Symfony\ConsoleExtension::class;
        }

        return $dependencies;
    }

    /**
     * {@inheritdoc}
     */
    public function register(Container $container, array $configs = []): void
    {
        if (!$container->has('events.dispatcher')) {
            $eventsDispatcher = $configs['events_dispatcher'] ?? EventHandler::class;

            if ($container->has($eventsDispatcher)) {
                $container->alias('events.dispatcher', $eventsDispatcher);
            } else {
                $container->autowire('events.dispatcher', new Definition($eventsDispatcher));
            }
        }

        if (!$container->has('request_stack')) {
            $container->autowire('request_stack', new Definition(RequestStack::class));
        }
    }
}

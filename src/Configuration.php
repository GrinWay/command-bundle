<?php

namespace GrinWay\Command;

use function Symfony\Component\String\u;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\Parameter;
use GrinWay\Command\GrinWayCommandExtension;

class Configuration implements ConfigurationInterface
{
    public function __construct(
        private readonly array $progressBarSpin,
        private readonly string $appEnv,
    ) {
    }

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(GrinWayCommandExtension::PREFIX);

        $treeBuilder->getRootNode()
            ->info(''
                . 'You can copy this example: "'
                . \dirname(__DIR__)
                . DIRECTORY_SEPARATOR . 'config'
                . DIRECTORY_SEPARATOR . 'packages'
                . DIRECTORY_SEPARATOR . 'grin_way_command.yaml'
                . '"')
            ->children()

                ->scalarNode(GrinWayCommandExtension::APP_ENV)
                    ->info('env(APP_ENV) of the project')
                    ->defaultValue($this->appEnv)
                ->end()

                ->booleanNode(GrinWayCommandExtension::DISPLAY_INIT_HELP)
                    ->info('Display to user init help information of this bundle')
                    ->defaultValue('%env(bool:GRIN_WAY_COMMAND_DISPLAY_INIT_HELP_MESSAGE)%')
                ->end()

                ->arrayNode(GrinWayCommandExtension::PROGRESS_BAR_SPIN)
                ->info('Array with the animation elements')
                ->beforeNormalization()
                    // adds space in the end of each el
                    ->always(static function ($array): array {
                        return \array_map(static fn($v) => (string) u($v)->ensureEnd(' '), $array);
                    })
                ->end()
                    ->defaultValue($this->progressBarSpin)
                    ->scalarPrototype()->end()
                ->end()

            ->end()
        ;

        //$treeBuilder->setPathSeparator('/');

        return $treeBuilder;
    }

    //###> HELPERS ###
}

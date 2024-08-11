<?php

namespace GrinWay\Command;

use GrinWay\Service\Service\{
    ServiceContainer,
    StringNormalizer
};
use Symfony\Component\DependencyInjection\{
    Parameter,
    Reference
};
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\EventDispatcher\DependencyInjection\AddEventAliasesPass;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Compiler\ResolveEnvPlaceholdersPass;
use GrinWay\Command\GrinWayCommandExtension;
use GrinWay\Command\Pass\MonologLoggerPass;

class GrinWayCommandBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

		$container->addCompilerPass(new MonologLoggerPass());
	}

    public function getContainerExtension(): ?ExtensionInterface
    {
        if ($this->extension === null) {
            $this->extension = new GrinWayCommandExtension();
        }

        return $this->extension;
    }
}

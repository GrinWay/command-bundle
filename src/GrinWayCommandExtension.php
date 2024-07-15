<?php

namespace GrinWay\Command;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\{
    Parameter,
    Reference
};
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\{
    YamlFileLoader
};
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use GrinWay\Command\Configuration;
use GrinWay\Service\Service\{
    ServiceContainer,
    StringNormalizer
};

class GrinWayCommandExtension extends ConfigurableExtension implements PrependExtensionInterface
{
    public const PREFIX = 'grin_way_command';
    public const APP_ENV = 'app_env';
    public const PROGRESS_BAR_SPIN = 'progress_bar_spin';
    public const DISPLAY_INIT_HELP = 'display_init_help';

    public function getAlias(): string
    {
        return self::PREFIX;
    }

    public function prepend(ContainerBuilder $container)
    {
        $this->loadYaml($container, [
            ['config', 'services.yaml'],
            ['config/packages', 'monolog.yaml'],
        ]);
    }

    public function getConfiguration(
        array $config,
        ContainerBuilder $container,
    ) {
		return new Configuration(
            appEnv: $container->getParameter('env(APP_ENV)'),
            progressBarSpin: $container->getParameter(ServiceContainer::getParameterName(
                self::PREFIX,
                self::PROGRESS_BAR_SPIN,
            )),
        );
    }

    public function loadInternal(array $config, ContainerBuilder $container): void
    {
        $this->fillInParameters($config, $container);
        $this->fillInServiceArgumentsWithConfigOfCurrentBundle($config, $container);
        $this->registerBundleTagsForAutoconfiguration($container);
    }

    //###> HELPERS ###

    private function fillInParameters(
        array $config,
        ContainerBuilder $container,
    ) {
        /*
        \dd(
            $container->hasParameter('error_prod_logger_email'),
            PropertyAccess::createPropertyAccessor()->getValue($config, '[error_prod_logger_email][from]'),
        );
        */

        $pa = PropertyAccess::createPropertyAccessor();

        ServiceContainer::setParametersForce(
            $container,
            callbackGetValue: static function ($key) use (&$config, $pa) {
                return $pa->getValue($config, '[' . $key . ']');
            },
            parameterPrefix: self::PREFIX,
            keys: [
                self::APP_ENV,
                self::PROGRESS_BAR_SPIN,
                self::DISPLAY_INIT_HELP,
            ],
        );
        /* to use in this object */
    }

    private function fillInServiceArgumentsWithConfigOfCurrentBundle(
        array $config,
        ContainerBuilder $container,
    ) {
    }

    private function registerBundleTagsForAutoconfiguration(ContainerBuilder $container)
    {
        /*
        $container
            ->registerForAutoconfiguration(\GrinWay\Command\<>Interface::class)
            ->addTag(GrinWayTag::<>)
        ;
        */
    }

    /**
        @var    $relPath is a relPath or array with the following structure:
            [
                ['relPath', 'filename'],
                ...
            ]
    */
    private function loadYaml(
        ContainerBuilder $container,
        string|array $relPath,
        ?string $filename = null,
    ): void {

        if (\is_array($relPath)) {
            foreach ($relPath as [$unpackedRelPath, $filename]) {
                $this->loadYaml($container, $unpackedRelPath, $filename);
            }
            return;
        }

        if (\is_string($relPath) && $filename === null) {
            throw new \Exception('Incorrect method arguments');
        }

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(
                [
                    __DIR__ . '/../' . $relPath,
                ],
            ),
        );
        $loader->load($filename);
    }
}

<?php

namespace GrinWay\Command\Trait;

use Symfony\Component\Console\Input\{
    InputArgument,
    InputOption,
    InputInterface
};
use Symfony\Component\Console\Output\{
    OutputInterface
};
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Workflow\WorkflowInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use GrinWay\Service\Service\{
    ConfigService,
    FilesystemService,
    DumpInfoService,
    StringService,
    ParametersService
};

trait DepthAbleTrait
{
    use AbstractGetCommandTrait;

    //###> ABSTRACT ###

    /* DepthAbleTrait */
    abstract protected function &getDepthProperty(): array|string;

    //###< ABSTRACT ###


    protected function configureDepthOption(): void
    {
        $this->configureOption(
            name:           'depth',
            default:        $this->getDepthProperty(),
            description:    $this->grinWayCommandGetCommandForTrait()->getTranslator()->trans('grin_way_command.trait.depth_able.description'),
            mode:           InputOption::VALUE_REQUIRED,
            shortcut:       'd',
        );
    }

    protected function initializeDepthOption(
        InputInterface $input,
        OutputInterface $output,
    ): void {
        $this->initializeOption(
            $input,
            $output,
            'depth',
            $this->getDepthProperty(),
            set:        static fn(?string $userOption, &$option)
                => $option = \array_map(\trim(...), \explode(',', $userOption)),
        );
    }
}

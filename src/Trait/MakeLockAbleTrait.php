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

trait MakeLockAbleTrait
{
    use AbstractGetCommandTrait;

    //###> ABSTRACT ###

    /* MakeLockAbleTrait */
    abstract protected function &getMakeLockProperty(): bool;

    //###< ABSTRACT ###

    protected function configureLockOption(): void
    {
        $this->configureOption(
            name:           'make-lock',
            default:        $this->getMakeLockProperty(),
            description:    $this->grinWayCommandGetCommandForTrait()->getTranslator()->trans('grin_way_command.trait.make_lock_able.description'),
            mode:           InputOption::VALUE_NEGATABLE,
        );
    }

    protected function initializeLockOption(
        InputInterface $input,
        OutputInterface $output,
    ): void {
        $this->initializeOption(
            $input,
            $output,
            'make-lock',
            $this->getMakeLockProperty(),
        );
    }
}

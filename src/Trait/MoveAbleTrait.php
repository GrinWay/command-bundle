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

trait MoveAbleTrait
{
    use AbstractGetCommandTrait;

    //###> ABSTRACT ###

    /* MoveAbleTrait */
    abstract protected function &getMoveProperty(): bool;

    //###< ABSTRACT ###

    protected function configureMoveOption(): void
    {
        $this->configureOption(
            name:           'move',
            default:        $this->getMoveProperty(),
            description:    $this->grinWayCommandGetCommandForTrait()->getTranslator()->trans('grin_way_command.trait.move_able.description'),
            mode:           InputOption::VALUE_NEGATABLE,
        );
    }

    protected function initializeMoveOption(
        InputInterface $input,
        OutputInterface $output,
    ): void {
        $this->initializeOption(
            $input,
            $output,
            'move',
            $this->getMoveProperty(),
        );
    }
}

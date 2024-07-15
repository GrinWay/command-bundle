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

trait AskAbleTrait
{
    use AbstractGetCommandTrait;

    //###> ABSTRACT ###

    /* AskAbleTrait */
    abstract protected function &getAskProperty(): bool;

    //###< ABSTRACT ###


    protected function configureAskOption(): void
    {
        $this->configureOption(
            name:           'ask',
            default:        $this->getAskProperty(),
            description:    $this->grinWayCommandGetCommandForTrait()->getTranslator()->trans('grin_way_command.trait.ask_able.description'),
            mode:           InputOption::VALUE_NEGATABLE,
        );
    }

    protected function initializeAskOption(
        InputInterface $input,
        OutputInterface $output,
    ): void {
        $this->initializeOption(
            $input,
            $output,
            'ask',
            $this->getAskProperty(),
        );
    }
}

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

trait DumpInfoAbleTrait
{
    use AbstractGetCommandTrait;

    //###> ABSTRACT ###

    /* DumpInfoAbleTrait */
    abstract protected function &getDumpInfoProperty(): bool;

    //###< ABSTRACT ###


    //###> PUBLIC API ###

    public function isDumpInfo(): bool
    {
        return $this->getDumpInfoProperty();
    }

    //###< PUBLIC API ###


    protected function configureDumpInfoOption(): void
    {
        $this->configureOption(
            name:           'dump-info',
            default:        $this->getDumpInfoProperty(),
            description:    $this->grinWayCommandGetCommandForTrait()->getTranslator()->trans('grin_way_command.trait.dump_info_able.description'),
            mode:           InputOption::VALUE_NEGATABLE,
        );
    }

    protected function initializeDumpInfoOption(
        InputInterface $input,
        OutputInterface $output,
    ): void {
        $this->initializeOption(
            $input,
            $output,
            'dump-info',
            $this->getDumpInfoProperty(),
        );
    }
}

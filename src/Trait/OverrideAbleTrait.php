<?php

namespace GrinWay\Command\Trait;

use Symfony\Component\Console\Question\ConfirmationQuestion;
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
use GrinWay\Command\Command\AbstractCommand;
use GrinWay\Service\Service\{
    ConfigService,
    FilesystemService,
    DumpInfoService,
    StringService,
    ParametersService
};

trait OverrideAbleTrait
{
    use AbstractGetCommandTrait;

    //###> ABSTRACT ###

    /* OverrideAbleTrait */
    abstract protected function &getOverrideProperty(): bool;

    /* OverrideAbleTrait */
    abstract protected function &isAskOverride(): bool;

    //###< ABSTRACT ###

    protected function configureOverrideOptions(): void
    {
        $this->configureOption(
            name:           'override',
            default:        $this->getOverrideProperty(),
            description:    $this->grinWayCommandGetCommandForTrait()->getTranslator()->trans('grin_way_command.trait.override_able.description'),
            mode:           InputOption::VALUE_NEGATABLE,
            shortcut:       'o',
        );

        $this->configureOption(
            name:           'ask-override',
            default:        $this->isAskOverride(),
            description:    $this->grinWayCommandGetCommandForTrait()->getTranslator()->trans('grin_way_command.trait.override_able.ask_description'),
            mode:           InputOption::VALUE_NEGATABLE,
        );
    }

    protected function initializeOverrideOptions(
        InputInterface $input,
        OutputInterface $output,
    ): void {

        $this->initializeOption(
            $input,
            $output,
            'ask-override',
            $this->isAskOverride(),
        );

        $command = $this->grinWayCommandGetCommandForTrait();
        $do = function (
            string $overrideUserOption,
            &$override,/*by ref*/
        ) use (
            &$command,
            &$output
) {
            if ($overrideUserOption == true) {
                if ($this->isAskOverride()) {
                    //###>
                    $output->writeln('');
                    $output->write(''
                        . '<bg=black;fg=green>'
                        . ' ' . $this->grinWayCommandGetCommandForTrait()->getTranslator()->trans(
                            'grin_way_command.trait.override_able.confirm_ask'
                        )
                        . '</>',);
                    $output->writeln('');

                    //###>
                    $answer = $command->isOk(
                        default: $override,
                    );
                } else {
                    $answer = true;
                }
                if ($answer == true) {
                    $override = true;
                }
            } else {
                $override = $overrideUserOption;
            }
        };
        $this->initializeOption(
            $input,
            $output,
            name:           'override',
            option:         $this->getOverrideProperty(),
            predicat:       static fn(?string $userOption, &$option/*by ref*/)
                => $userOption !== null && $option !== true,
            set:            static fn(?string $userOption, &$option/*by ref*/)
                => $do($userOption, $option),
        );
    }
}

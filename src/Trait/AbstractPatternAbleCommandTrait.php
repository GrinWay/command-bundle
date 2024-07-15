<?php

namespace GrinWay\Command\Trait;

use function Symfony\Component\String\u;

use Symfony\Component\Finder\SplFileInfo;
use Carbon\Carbon;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\{
    Path,
    Filesystem
};
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Component\Console\Command\SignalableCommandInterface;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Helper\{
    ProgressBar,
    Table
};
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Validator\{
    Constraints,
    Validation
};
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\{
    TableSeparator
};
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Completion\{
    CompletionSuggestions,
    CompletionInput
};
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\{
    AsCommand
};
use Symfony\Component\Console\Input\{
    InputArgument,
    InputOption,
    InputInterface
};
use Symfony\Component\Console\Output\{
    OutputInterface
};
use GrinWay\Command\Contracts\PatternAbleCommandInterface;

/*
    Realize your own parser in a certain class:
        PARSERS DESCRIPTIONS
        PARSERS API
        PARSER HELPERS
*/
trait AbstractPatternAbleCommandTrait
{
    use AbstractGetCommandTrait;

    //###> ABSTRACT ###

    /* AbstractPatternAbleCommandTrait */
    abstract protected function &getStringPatternProperty(): ?string;

    /* AbstractPatternAbleCommandTrait */
    abstract protected function &getExplodedPatternsProperty(): array;

    //###< ABSTRACT ###


    //###> PARSERS DESCRIPTIONS ###
    //###< PARSERS DESCRIPTIONS ###

    public function patternAbleCommandDuringExecute(
        InputInterface $input,
        OutputInterface $output,
    ): void {
        /* GUARANTEE THAT THE stringPattern WAS GIVEN */
        if ($this->getStringPatternProperty() !== null) {
            $this->setExplodedPatterns(
                $this->getCalculatedExplodedPatterns(
                    $this->getStringPatternProperty(),
                ),
            );
        }
    }

    public function patternAbleCommandDuringConfigure(): void
    {
        $this->configurePatternArgument();
    }

    public function patternAbleCommandDuringInitialize(
        InputInterface $input,
        OutputInterface $output,
    ) {
        $this->initializePatternArgument(
            $input,
            $output,
        );
    }


    //###> PARSERS API ###
    //###< PARSERS API ###


    //###> API ###

    protected function getStringPattern(): ?string
    {
        return $this->getStringPatternProperty();
    }

    protected function getExplodedPatterns(): array
    {
        return $this->getExplodedPatternsProperty();
    }

    //###< API ###


    //###> ABSTRACT ###

    /* GUARANTEED THAT stringPattern ALREADY GIVES NOT NULL */
    /* AbstractPatternAbleConstructedFromToCommand
        USE PARSERS API HERE

        EXAMPLE:
            return $this-><use*Parser>($stringPattern);
    */
    abstract protected function getCalculatedExplodedPatterns(
        string $stringPattern,
    ): array;

    /* AbstractPatternAbleConstructedFromToCommand */
    abstract protected function getPatternDescription(): string;

    /* AbstractPatternAbleConstructedFromToCommand */
    abstract protected function getPatternName(): string;

    /* AbstractPatternAbleConstructedFromToCommand */
    abstract protected function getPatternMode(): int;

    //###< ABSTRACT ###


    //###> HELPER ###

    protected function configurePatternArgument(): void
    {
        $this->grinWayCommandGetCommandForTrait()->configureArgument(
            name: $this->getPatternName(),
            mode: $this->getPatternMode(),
            description: $this->grinWayCommandGetCommandForTrait()->getTranslator()->trans('grin_way_command.trait.pattern_able_trait.string_like_word')
            . ': "' . $this->getPatternDescription() . '"',
            add_default_to_description: false,
        );
    }

    protected function initializePatternArgument(
        InputInterface $input,
        OutputInterface $output,
    ): void {
        $this->grinWayCommandGetCommandForTrait()->initializeArgument(
            $input,
            $output,
            $this->getPatternName(),
            $this->getStringPatternProperty(),
        );
    }

    private function setExplodedPatterns(
        array $explodedPatterns,
    ): static {
        $v =& $this->getExplodedPatternsProperty();
        $v = $explodedPatterns;
        return $this;
    }

    //###< HELPER ###


    //###> PARSER HELPERS ###
    //###< PARSER HELPERS ###
}

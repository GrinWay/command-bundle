<?php

namespace GrinWay\Command\Trait\PatternAbleInstance;

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
use GrinWay\Service\Service\BoolService;
use GrinWay\Service\Service\ArrayService;
use GrinWay\Service\Service\ConfigService;
use GrinWay\Command\Trait\AbstractGetCommandTrait;

/**/
trait AbstractPatternAbleCommandUseOneThreeReplacementPartsOfRegexTrait
{
    use AbstractGetCommandTrait;

    //###> ABSTRACT ###

    /* AbstractPatternAbleCommandUseOneThreeReplacementPartsOfRegexTrait */
    abstract protected function isCaseSensitive(): bool;

    /* AbstractPatternAbleCommandUseOneThreeReplacementPartsOfRegexTrait */
    abstract protected function isDisplayPatternInfoBeforeCommand(): bool;

    /* AbstractPatternAbleCommandUseOneThreeReplacementPartsOfRegexTrait */
    abstract protected function getFirstPartName(): string;

    /* AbstractPatternAbleCommandUseOneThreeReplacementPartsOfRegexTrait */
    abstract protected function getSecondPartName(): string;

    /* AbstractPatternAbleCommandUseOneThreeReplacementPartsOfRegexTrait */
    abstract protected function getThirdPartName(): string;

    /* AbstractPatternAbleCommandUseOneThreeReplacementPartsOfRegexTrait */
    abstract protected function getFirstPartRegex(): string;

    /* AbstractPatternAbleCommandUseOneThreeReplacementPartsOfRegexTrait */
    abstract protected function getSecondPartRegex(): string;

    /* AbstractPatternAbleCommandUseOneThreeReplacementPartsOfRegexTrait */
    abstract protected function getThirdPartRegex(): string;

    /* AbstractPatternAbleCommandUseOneThreeReplacementPartsOfRegexTrait */
    abstract protected function isSkipByParsedForFirstPart(
        string $parsed,
    ): bool;

    /* AbstractPatternAbleCommandUseOneThreeReplacementPartsOfRegexTrait */
    abstract protected function isSkipByParsedForSecondPart(
        string $parsed,
    ): bool;

    /* AbstractPatternAbleCommandUseOneThreeReplacementPartsOfRegexTrait */
    abstract protected function isSkipByParsedForThirdPart(
        string $parsed,
    ): bool;

    /* AbstractPatternAbleCommandUseOneThreeReplacementPartsOfRegexTrait */
    abstract protected function getNormalizedFirstPartParsed(
        string $parsed,
    ): mixed;

    /* AbstractPatternAbleCommandUseOneThreeReplacementPartsOfRegexTrait */
    abstract protected function getNormalizedSecondPartParsed(
        string $parsed,
    ): mixed;

    /* AbstractPatternAbleCommandUseOneThreeReplacementPartsOfRegexTrait */
    abstract protected function getNormalizedThirdPartParsed(
        string $parsed,
    ): mixed;

    /* AbstractPatternAbleCommandUseOneThreeReplacementPartsOfRegexTrait */
    abstract protected function getFirstPartFromSource(
        SplFileInfo $finderSplFileInfo,
    ): mixed;

    /* AbstractPatternAbleCommandUseOneThreeReplacementPartsOfRegexTrait */
    abstract protected function getSecondPartFromSource(
        SplFileInfo $finderSplFileInfo,
    ): mixed;

    /* AbstractPatternAbleCommandUseOneThreeReplacementPartsOfRegexTrait */
    abstract protected function getThirdPartFromSource(
        SplFileInfo $finderSplFileInfo,
    ): mixed;

    /* AbstractPatternAbleCommandUseOneThreeReplacementPartsOfRegexTrait */
    abstract protected function getBoolService(): BoolService;

    /* AbstractPatternAbleCommandUseOneThreeReplacementPartsOfRegexTrait */
    abstract protected function getArrayService(): ArrayService;

    //###< ABSTRACT ###


    //###> PUBLIC API ###

    /* AbstractPatternAbleCommandUseOneThreeReplacementPartsOfRegexTrait */
    public function getPatternDelimiter(): string
    {
        return ',';
    }

    //###< PUBLIC API ###


    //###> ABSTRACT REALIZATION ###

    /* Command */
    protected function command(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $this->patternAbleCommandDuringExecute(
            $input,
            $output,
        );

        $this->makeExplodedPatternsSortedWhenFirstMoreRestrictions();

        if ($this->isDisplayPatternInfoBeforeCommand()) {
            $this->displayPatternInfo(
                $input,
                $output,
            );
        }

        return parent::command(
            $input,
            $output,
        );
    }

    /* AbstractPatternAbleConstructedFromToCommand */
    protected function getPatternDescription(): string
    {
        return $this->grinWayCommandGetCommandForTrait()->getTranslator()->trans(
            'grin_way_command.trait.pattern_able_one_three_replacement.description',
        )
        ;
    }

    /* AbstractPatternAbleConstructedFromToCommand */
    protected function getCalculatedExplodedPatterns(
        string $stringPattern,
    ): array {
        return $this->useOneThreeReplacementPartsRegex(
            $stringPattern,
        );
    }

    /* Command */
    protected function configure()
    {
        $this->patternAbleCommandDuringConfigure();

        parent::configure();
    }

    /* Command */
    protected function initialize(
        InputInterface $input,
        OutputInterface $output,
    ) {
        parent::initialize(
            $input,
            $output,
        );

        $this->patternAbleCommandDuringInitialize(
            $input,
            $output,
        );
    }

    //###< ABSTRACT REALIZATION ###


    //###> CAN OVERRIDE ###

    /* AbstractPatternAbleConstructedFromToCommand */
    protected function displayPatternInfo(
        InputInterface $input,
        OutputInterface $output,
    ): void {
        $patterns = $this->getExplodedPatterns();
        if (empty($patterns)) {
            return;
        }

        $patternInfos = [];
        $t = $this->grinWayCommandGetCommandForTrait()->getTranslator();

        $getTranlatedInfoEl = static function (
            string|int $v,
            string $dop,
        ) use (
            &$t,
        ): string {

            return u($t->trans($dop))->title() . '' . $v;
        };

        foreach ($patterns as $info) {
            $infoEl = [];
            $bs = $this->getBoolService();

            $assignInfoEl = static function (
                array ...$names,
            ) use (
                &$infoEl,
                &$bs,
                &$info,
                &$getTranlatedInfoEl,
            ): void {
                foreach ($names as [$paramName, $paramTitle, $anyWord]) {
                    if ($v = $bs->isGet($info, $paramName)) {
                        $infoEl [] = $getTranlatedInfoEl($v, $paramTitle);
                    } else {
                        $infoEl [] = $getTranlatedInfoEl($anyWord, $paramTitle);
                    }
                }
            };

            $assignInfoEl(
                [$this->getFirstPartName(), '', $this->getHaveNotPassedMessage()],
                [$this->getSecondPartName(), '', $this->getHaveNotPassedMessage()],
                [$this->getThirdPartName(), '', $this->getHaveNotPassedMessage()],
            );

            $patternInfos [] = $infoEl;
        }

        //###> show
        $this->getCloneTable()
            ->setHeaders([
                $this->grinWayCommandGetCommandForTrait()->getTranslator()->trans(
                    'grin_way_command.trait.pattern_able_one_three_replacement.first_part_for_display',
                ),
                $this->grinWayCommandGetCommandForTrait()->getTranslator()->trans(
                    'grin_way_command.trait.pattern_able_one_three_replacement.second_part_for_display',
                ),
                $this->grinWayCommandGetCommandForTrait()->getTranslator()->trans(
                    'grin_way_command.trait.pattern_able_one_three_replacement.third_part_for_display',
                ),
            ])
            ->setRows($patternInfos)
            ->render()
        ;
    }

    protected function getHaveNotPassedMessage(): string
    {
        return $this->grinWayCommandGetCommandForTrait()->getTranslator()->trans(
            'grin_way_command.trait.pattern_able_one_three_replacement.have_not_passed'
        );
    }

    //###< CAN OVERRIDE ###


    ###> API ###

    /* FOR FILTERING BY PATTERN
        use it into your Command

        returns null    when pattern wasn't passed
        returns false   when pattern wasn't matched
        returns true    when pattern was matched
    */
    protected function isPassedMatchedOneThreeReplacementRegex(
        SplFileInfo $finderSplFileInfo,
    ): ?bool {
        $isPassedMached = null; /* THERE WERE NO ANY PATTERN */
        if (!empty($this->getExplodedPatterns())) {
            $firstPart = $this->getFirstPartFromSource(
                $finderSplFileInfo,
            );
            $secondPart = $this->getSecondPartFromSource(
                $finderSplFileInfo,
            );
            $thirdPart = $this->getThirdPartFromSource(
                $finderSplFileInfo,
            );

            if ($thirdPart === null) {
                return false;
            }

            $patternFilter = $this->getArrayService()->getParsedOneThreeReplacementPartsRegex(
                [
                    $this->getFirstPartName() => $firstPart,
                    $this->getSecondPartName() => $secondPart,
                    $this->getThirdPartName() => $thirdPart,
                ],
                $this->getExplodedPatterns(),
                forFilter: true,
            );
            $isPassedMached = $patternFilter === [];
        }
        return $isPassedMached;
    }

    ###< API ###


    //###> PARSERS API ###

    /*
        OUTPUT:
            [
                [
                    'First'         => <>,
                ],
                [
                    'First'         => <>,
                    'Third'         => <>,
                ],
                [
                    'First'         => <>,
                    'Second'        => <>,
                ],
                [
                    'First'         => <>,
                    'Second'        => <>,
                    'Third'         => <>,
                ],
                ...
            ]
    */
    private function useOneThreeReplacementPartsRegex(
        string $stringPattern,
        bool $asIntoConfig = true,
    ): array {
        $threeReplacementPartsRegex = [];

        $explodedStringPatterns = \explode(
            $this->getPatternDelimiter(),
            $stringPattern,
        );

        $isCaseSensitive = $this->isCaseSensitive();
        $is = static function (
            string $partOfRegex,
            string $string,
        ) use (
            $isCaseSensitive,
        ) {
            $flags = 'u';

            if (!$isCaseSensitive) {
                $flags .= 'i';
            }

            return \preg_match($fulRegex = '~^' . $partOfRegex . '$~' . $flags, $string) === 1
                ? $fulRegex
                : false
            ;
        };

        $idx = -1; /* ONLY FOR PARSE FUNCTION */
        foreach ($explodedStringPatterns as $explodedStringPattern) {
            ++$idx;

            $explodedStringPattern = \trim($explodedStringPattern);

            $FP = '(?<' . $this->getFirstPartName() . '>' . $this->getFirstPartRegex() . ')';
            $SP = '(?<' . $this->getSecondPartName() . '>' . $this->getSecondPartRegex() . ')';
            $TP = '(?<' . $this->getThirdPartName() . '>' . $this->getThirdPartRegex() . ')';

            foreach (
                [
                $FP . '\s*' . $SP . '\s*' . $TP,
                $FP . '\s+' . $TP . '\s*' . $SP,
                $SP . '\s*' . $TP . '\s+' . $FP,
                $SP . '\s*' . $FP . '\s+' . $TP,
                $TP . '\s+' . $FP . '\s*' . $SP,
                $TP . '\s*' . $SP . '\s*' . $FP,
                $FP . '\s*' . $SP,
                $SP . '\s*' . $FP,
                $FP . '\s+' . $TP,
                $TP . '\s+' . $FP,
                $SP . '\s*' . $TP,
                $TP . '\s*' . $SP,
                $FP,
                $SP,
                $TP,
                ] as $possiblePattern
            ) {
                if ($regex = $is($possiblePattern, $explodedStringPattern)) {
                    $this->parseAndAssing(
                        $threeReplacementPartsRegex,
                        $regex,
                        $explodedStringPattern,
                        $asIntoConfig,
                        $idx,
                    );
                    break;
                }
            }
        }

        return $threeReplacementPartsRegex;
    }

    //###< PARSERS API ###


    //###> PARSER HELPERS ###

    private function parseAndAssing(
        array &$array,
        string $regex,
        string $string,
        bool $asIntoConfig,
        int $idx,
    ): void {
        $matches = [];
        \preg_match($regex, $string, $matches);

        foreach (
            [
            [
                $this->getFirstPartName(),
                $this->isSkipByParsedForFirstPart(...),
                $this->getNormalizedFirstPartParsed(...),
            ],
            [
                $this->getSecondPartName(),
                $this->isSkipByParsedForSecondPart(...),
                $this->getNormalizedSecondPartParsed(...),
            ],
            [
                $this->getThirdPartName(),
                $this->isSkipByParsedForThirdPart(...),
                $this->getNormalizedThirdPartParsed(...),
            ],
            ] as [
            $partName,
            $isSkip,
            $getNormalizedPartParsed,
            ]
        ) {
            $parsed = $this->getBoolService()->isGet($matches, $partName);

            if ($parsed !== null && !$isSkip($parsed)) {
                $parsed = $getNormalizedPartParsed($parsed);
                $array[$idx][$partName] = $parsed;
                //\dd($parsed, $array);
            }
        }
    }

    //###< PARSER HELPERS ###


    //###> HELPERS ###

    /* For correct filtering
        First contains more restrictions
    */
    private function makeExplodedPatternsSortedWhenFirstMoreRestrictions(): static
    {
        \usort(
            $this->getExplodedPatternsProperty(),
            static fn($f, $s) => \count($f) > \count($s),
        );

        return $this;
    }

    //###< HELPERS ###
}

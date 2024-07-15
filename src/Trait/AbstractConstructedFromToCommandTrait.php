<?php

namespace GrinWay\Command\Trait;

use function Symfony\Component\String\u;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\{
    Path,
    Filesystem
};
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
use GrinWay\Service\Service\{
    StringService,
    DumpInfoService,
    FilesystemService
};
use GrinWay\Command\Contracts\{
    AbstractConstructedFromToPathsDataSupplier
};
use GrinWay\Command\Trait\{
    OverrideAbleTrait
};

/* DO SOMETHING WITH THE FILES

    CONCEPT: DUMP AT FIRST, THEN EXECUTE

    | __SOURCE__ |=> constructedFromToPaths <=| __SOURCE__ |

    setConstructedFromToPaths
    dumpConstructedFromToPaths
    isOk
        makeConstructedFromToPaths
*/
trait AbstractConstructedFromToCommandTrait
{
    use AbstractGetCommandTrait;

    /*
        [
            [
                'from'              => '<string>',
                'to'                => '<string>',
            ],
            ...
        ]
    */
    private array $constructedFromToPaths       = [];
    private ?string $fromForFinder              = null;
    private ?Finder $finder                     = null;
    private ?AbstractConstructedFromToPathsDataSupplier $dataSupplierForConstructedFromToPaths = null;
    private int $quantityConstructedFromToPaths = 0;


    //###> ABSTRACT ###

    /* AbstractConstructedFromToCommandTrait */
    abstract protected function &grinWayCommandGetStringServiceForTrait(): StringService;

    /* AbstractConstructedFromToCommandTrait */
    abstract protected function &grinWayCommandGetDumpInfoServiceForTrait(): DumpInfoService;

    /* AbstractConstructedFromToCommandTrait */
    abstract protected function &grinWayCommandGetFilesystemServiceForTrait(): FilesystemService;

    /* AbstractConstructedFromToCommandTrait
        create your own ConstructedFromToPathsDataSupplier for a certain command
        extends AbstractConstructedFromToPathsDataSupplier
    */
    abstract protected function getDataSuppliersForConstructedFromToPaths(): \Traversable|array;

    /* AbstractConstructedFromToCommandTrait
        [INTO CYCLE]

        ###>READY:
            getFromForFinder
    */
    abstract protected function getFinder(
        AbstractConstructedFromToPathsDataSupplier $dataSupplier,
    ): Finder;

    /* AbstractConstructedFromToCommandTrait
        [INTO CYCLE]
    */
    abstract protected function initScanningConstructedFromToPaths(
        InputInterface $input,
        OutputInterface $output,
        AbstractConstructedFromToPathsDataSupplier $dataSupplier,
    ): void;

    /* AbstractConstructedFromToCommandTrait
        [CYCLE INTO CYCLE]
    */
    abstract protected function scanningCycleForConstructedFromToPaths(
        InputInterface $input,
        OutputInterface $output,
    ): void;

    /* AbstractConstructedFromToCommandTrait
        [CYCLE INTO CYCLE]

        FILTER
    */
    abstract protected function isSkipForConstructedFromToPaths(
        SplFileInfo $finderSplFileInfo,
        string $from,
        string $to,
        AbstractConstructedFromToPathsDataSupplier $dataSupplier,
    ): bool;

    /* AbstractConstructedFromToCommandTrait
        [INTO CYCLE]
    */
    abstract protected function endScanningForConstructedFromToPaths(
        InputInterface $input,
        OutputInterface $output,
    ): void;

    /* AbstractConstructedFromToCommandTrait
        [INTO CYCLE]
    */
    abstract protected function clearStateWhenStartCycle(): void;

    /* AbstractConstructedFromToCommandTrait
        [INTO CYCLE]
    */
    abstract protected function makeWillNotBe(
        InputInterface $input,
        OutputInterface $output,
        AbstractConstructedFromToPathsDataSupplier $dataSupplier,
    ): void;

    /* AbstractConstructedFromToCommandTrait
        [INTO CYCLE]
    */
    abstract protected function beforeDumpInfoConstructedFromToPaths(
        InputInterface $input,
        OutputInterface $output,
    ): void;

    /* AbstractConstructedFromToCommandTrait
        [INTO CYCLE]
    */
    abstract protected function isDumpInfoOnlyDirname(): bool;

    /* AbstractConstructedFromToCommandTrait
        RETURN NULL IF YOU DON'T WANNT CONSIDER IT

        [INTO CYCLE]
    */
    abstract protected function isDumpInfoOnlyFrom(): ?bool;

    /* AbstractConstructedFromToCommandTrait
        RETURN NULL IF YOU DON'T WANNT CONSIDER IT

        [INTO CYCLE]
    */
    abstract protected function isDumpInfoOnlyTo(): ?bool;

    /* AbstractConstructedFromToCommandTrait
        [INTO CYCLE]

        ###>READY:
            getFromForFinder
            isConstructedFromToPathsEmpty
            getQuantityConstructedFromToPaths
    */
    abstract protected function beforeMakeFromToAlgorithm(
        InputInterface $input,
        OutputInterface $output,
        AbstractConstructedFromToPathsDataSupplier $dataSupplier,
    ): void;

    /* AbstractConstructedFromToCommandTrait
        [INTO CYCLE]
    */
    abstract protected function beforeMakeFromToAlgorithmAndAfterStartProgressBar(
        InputInterface $input,
        OutputInterface $output,
    ): void;

    /* AbstractConstructedFromToCommandTrait
        [CYCLE INTO CYCLE]
    */
    abstract protected function beforeMakeCycle(
        InputInterface $input,
        OutputInterface $output,
    ): void;

    /* AbstractConstructedFromToCommandTrait
        [CYCLE INTO CYCLE]
    */
    abstract protected function makeFromToAlgorithm(
        string $from,
        string $to,
        AbstractConstructedFromToPathsDataSupplier $dataSupplier,
    ): ?array;

    /* AbstractConstructedFromToCommandTrait
        [CYCLE INTO CYCLE]
    */
    abstract protected function afterMakeCycle(
        InputInterface $input,
        OutputInterface $output,
    ): void;

    /* AbstractConstructedFromToCommandTrait
        [INTO CYCLE]
    */
    abstract protected function afterDataSupplierCycleExecute(
        InputInterface $input,
        OutputInterface $output,
        bool $operationWasMade,
        AbstractConstructedFromToPathsDataSupplier $dataSupplier,
        int $madeQuantity,
        bool $madeQuantityEqualsAllFilesFrom,
    ): void;

    /* AbstractConstructedFromToCommandTrait

        ###>READY:
            getFromForFinder IN LAST ITERATION
            isConstructedFromToPathsEmpty IN LAST ITERATION
            getQuantityConstructedFromToPaths IN LAST ITERATION
    */
    abstract protected function afterDataSupplierExecute(
        InputInterface $input,
        OutputInterface $output,
        AbstractConstructedFromToPathsDataSupplier $dataSupplier,
    ): void;

    //###< ABSTRACT ###


    //###> CAN OVERRIDE ###

    // число отставания от цикла выполнения
    protected function getProgressBarDisplayFrequency(): int
    {
        return 0;
    }

    //###< CAN OVERRIDE ###


    //###> ABSTRACT REALIZATION ###

    /* AbstractCommand */
    protected function command(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $dataSuppliers = $this->getDataSuppliersForConstructedFromToPaths();

        // DATA SUPPLIERS
        foreach ($dataSuppliers as $dataSupplier) {
            $this->setDataSupplierForConstructedFromToPaths(
                $dataSupplier,
            );

            // important
            $this->clearCycleStateInTheBeginning();

            $this->setConstructedFromToPaths(
                $input,
                $output,
                $dataSupplier,
            );

            if ($this->isConstructedFromToPathsEmpty()) {
                $this->makeWillNotBe(
                    $input,
                    $output,
                    $dataSupplier,
                );
                continue;
            }

            $this->dumpConstructedFromToPaths(
                $input,
                $output,
                $dataSupplier,
            );

            //###>
            $operationWasMade = false;
            $madeQuantity = 0;
            if (
                $this->grinWayCommandGetCommandForTrait()->isOk(
                    default:        $dataSupplier->getDefaultIsOk(),
                )
            ) {
                $madeQuantity = $this->makeConstructedFromToPaths(
                    $input,
                    $output,
                    $dataSupplier,
                );
                $operationWasMade = true;
            }

            //###> hook
            $this->afterDataSupplierCycleExecute(
                $input,
                $output,
                $operationWasMade,
                $dataSupplier,
                $madeQuantity,
                madeQuantityEqualsAllFilesFrom: $madeQuantity === $this->getQuantityConstructedFromToPaths(),
            );
        }

        //###> hook
        $this->afterDataSupplierExecute(
            $input,
            $output,
            $dataSupplier,
        );

        return Command::SUCCESS;
    }

    //###< ABSTRACT REALIZATION ###


    //###> !OVERRIDE IT! ###

    protected function userChecksFrom(
        string $from,
    ): void {
    }

    protected function userChecksTo(
        string $to,
    ): void {
    }

    //###< !OVERRIDE IT! ###


    //###> API ###

    protected function getAlertStringForDataSupplier(
        string $title,
        AbstractConstructedFromToPathsDataSupplier $dataSupplier,
    ): string {
        return '' . $title . ' "' . $dataSupplier->getInfo() . '"';
    }

    protected function tryToRemovePaths(
        /* MESSAGE IS BASED ON from IN constructedFromToPaths */
        string $whatFromIsInConstructedFromToPaths,
        array $pathsForRemove,
    ): void {
        $longestCommon  = Path::getLongestCommonBasePath(...$pathsForRemove);
        $whatFromIsInConstructedFromToPaths = $this->grinWayCommandGetCommandForTrait()->getTranslator()->trans(
            $whatFromIsInConstructedFromToPaths,
        );

        $longestCommon  = $this->getDirIfFile($longestCommon);

        $fromDirPartMessage = ''
            . $this->grinWayCommandGetCommandForTrait()->getTranslator()->trans(
                'grin_way_command.from_word',
            )
            . ' ' . '[' . $longestCommon . ']'
            . ' ' . $this->grinWayCommandGetCommandForTrait()->getTranslator()->trans(
                'grin_way_command.directory_word',
            )
        ;
		
        $message = ''
            . \mb_strtoupper($this->grinWayCommandGetCommandForTrait()->getTranslator()->trans(
                'grin_way_command.trait.constructed_from_to_trait.delete_word',
            ))
			. ':'
            . ' ' . $whatFromIsInConstructedFromToPaths
            . ' ' . $fromDirPartMessage
			. '?'
        ;

        $infoMessage = ':'
			. u(
				u($whatFromIsInConstructedFromToPaths)->ensureEnd(' ')
				. \trim($fromDirPartMessage)
			)->ensureStart(' ')
		;

		$this->grinWayCommandGetCommandForTrait()->ioDump(
			$this->grinWayCommandGetCommandForTrait()->getTranslator()->trans($message),
			//new \GrinWay\Command\Contracts\IO\ErrorIODumper,
		);
		
		if ($this->grinWayCommandGetCommandForTrait()->isOk()) {
            $this->grinWayCommandGetCommandForTrait()->getIo()->info([
                ''
                . $this->grinWayCommandGetCommandForTrait()->getTranslator()->trans(
                    'grin_way_command.trait.constructed_from_to_trait.deleting_process',
                )
                . $infoMessage . '...',
            ]);
            foreach ($pathsForRemove as $pathForRemove) {
                $this->grinWayCommandGetFilesystemServiceForTrait()->deleteByAbsPathIfExists(
                    $pathForRemove,
                );
            }
            //###>
            $this->grinWayCommandGetCommandForTrait()->getIo()->note([
                ''
                . $this->grinWayCommandGetCommandForTrait()->getTranslator()->trans(
                    'grin_way_command.trait.constructed_from_to_trait.have_been_deleted',
                )
                . $infoMessage,
            ]);
        } else {
            //###>
            $this->grinWayCommandGetCommandForTrait()->getIo()->note([
                ''
                . $this->grinWayCommandGetCommandForTrait()->getTranslator()->trans(
                    'grin_way_command.trait.constructed_from_to_trait.have_not_been_deleted',
                )
                . $infoMessage,
            ]);
        }
    }

    protected function getFromForFinder(): ?string
    {
        return $this->fromForFinder;
    }

    protected function isConstructedFromToPathsEmpty(): bool
    {
        return empty(\array_filter($this->constructedFromToPaths));
    }

    protected function getConstructedFromToPaths(): array
    {
        return $this->constructedFromToPaths;
    }

    protected function getReadyFinder(): Finder
    {
        return $this->finder;
    }

    protected function getQuantityConstructedFromToPaths(): int
    {
        return $this->quantityConstructedFromToPaths;
    }

    //###< API ###


    //###> HELPER ###

    private function clearCycleStateInTheBeginning(): void
    {
        $this->clearStateWhenStartCycle();
        $this->clearConstructedFromToPaths();
        $this->quantityConstructedFromToPaths = 0;
    }

    private function setConstructedFromToPaths(
        InputInterface $input,
        OutputInterface $output,
        AbstractConstructedFromToPathsDataSupplier $dataSupplier,
    ): void {
        $this->fromForFinder = $dataSupplier->getFromForFinder(
            currentFromForFinder:   $this->getFromForFinder(),
            command:                $this,
        );

        $this->checkFromForFinder();

        $this->finder = $this->getFinder($dataSupplier)
            ->in($this->fromForFinder)
            ->files()
        ;

        $this->setConstructedFromToPathsByFinder(
            $input,
            $output,
            $dataSupplier,
        );
    }

    private function dumpConstructedFromToPaths(
        InputInterface $input,
        OutputInterface $output,
        AbstractConstructedFromToPathsDataSupplier $dataSupplier,
    ): void {
        $this->grinWayCommandGetCommandForTrait()->getIo()->title(
            $this->getAlertStringForDataSupplier(
                $this->grinWayCommandGetCommandForTrait()->getTranslator()->trans('grin_way_command.trait.constructed_from_to_trait.will_be_executed_word'),
                $dataSupplier,
            ),
        );

        $this->beforeDumpInfoConstructedFromToPaths(
            $input,
            $output,
        );

        $this->grinWayCommandGetDumpInfoServiceForTrait()->dumpInfo(
            $this,
            $this->constructedFromToPaths,
            dirname:        $this->isDumpInfoOnlyDirname(),
            onlyFrom:       $this->isDumpInfoOnlyFrom(),
            onlyTo:         $this->isDumpInfoOnlyTo(),
        );
    }

    private function setDataSupplierForConstructedFromToPaths(
        AbstractConstructedFromToPathsDataSupplier $dataSupplier,
    ): void {
        $this->dataSupplierForConstructedFromToPaths = $dataSupplier;
    }

    private function makeConstructedFromToPaths(
        InputInterface $input,
        OutputInterface $output,
        AbstractConstructedFromToPathsDataSupplier $dataSupplier,
    ): int {
        //###> INIT ###
        $counter                = 0;
        $updateProgressBar      = function (
            bool $force = false,
        ) use (&$counter) {
            if (
                $force
                || ++$counter > $this->getProgressBarDisplayFrequency()
            ) {
                $counter = 0;
                $this->grinWayCommandGetCommandForTrait()->getProgressBar()->advance();
                $this->grinWayCommandGetCommandForTrait()->getProgressBar()->display();
            }
        };
        //###< INIT ###

        $this->beforeMakeFromToAlgorithm(
            $input,
            $output,
            $dataSupplier,
        );

        //###>
        $this->grinWayCommandGetCommandForTrait()->getProgressBar()->setMaxSteps($this->getMaxSteps());
        $this->grinWayCommandGetCommandForTrait()->getProgressBar()->start();
        $this->beforeMakeFromToAlgorithmAndAfterStartProgressBar(
            $input,
            $output,
        );

        $madeQuantity = 0;
        foreach ($this->constructedFromToPaths as [ 'from' => $from, 'to' => $to ]) {
            $this->beforeMakeCycle(
                $input,
                $output,
            );
            $made = $this->makeFromToAlgorithm(
                from:           $from,
                to:             $to,
                dataSupplier:   $dataSupplier,
            );
            $updateProgressBar();
            $this->afterMakeCycle(
                $input,
                $output,
            );

            if (!empty($made)) {
                ++$madeQuantity;
            }
        }
        $updateProgressBar(force: true);
        $this->grinWayCommandGetCommandForTrait()->getProgressBar()->finish();
        $this->grinWayCommandGetCommandForTrait()->getProgressBar()->clear();

        return $madeQuantity;
    }

    private function getLongestCommonFromWithConstructedFromToPaths(): string
    {
        return Path::getLongestCommonBasePath(
            ...\array_filter(
                \array_map(
                    static fn($v) => $v['from'] ?? null,
                    $this->constructedFromToPaths,
                ),
            ),
        );
    }

    private function setConstructedFromToPathsByFinder(
        InputInterface $input,
        OutputInterface $output,
        AbstractConstructedFromToPathsDataSupplier $dataSupplier,
    ): void {
        $this->initScanningConstructedFromToPaths(
            $input,
            $output,
            $dataSupplier,
        );
        // FINDER
        foreach ($this->finder as $finderSplFileInfo) {
            // FROM:    FIRST
            $from           = $this->dataSupplierForConstructedFromToPaths->getFrom(
                $finderSplFileInfo,
            );

            // after from before to
            if (!$this->isFromExistingFileWithAbsPath($from)) {
                continue;
            }

            // TO:      SECOND
            $to             = $this->dataSupplierForConstructedFromToPaths->getTo(
                $finderSplFileInfo,
            );

            $this->userChecksFrom($from);

            $this->userChecksTo($to);

            $this->scanningCycleForConstructedFromToPaths(
                $input,
                $output,
            );

            //###>
            if (
                $this->isSkipForConstructedFromToPaths(
                    $finderSplFileInfo,
                    $from,
                    $to,
                    $this->dataSupplierForConstructedFromToPaths,
                )
            ) {
                continue;
            }

            $this->constructedFromToPaths [] = [
                'from'              => $from,
                'to'                => $to,
            ];
            ++$this->quantityConstructedFromToPaths;
        }
        $this->endScanningForConstructedFromToPaths(
            $input,
            $output,
        );
    }

    private function clearConstructedFromToPaths(): void
    {
        $this->constructedFromToPaths       = [];
    }

    private function isFromExistingFileWithAbsPath(
        ?string $from,
    ): bool {
        // faster
        if ($from === null) {
            return false;
        }

        return empty(
            $this->grinWayCommandGetFilesystemServiceForTrait()->getErrorsIfNot(
                [
                    'exists',
                    'isAbsolutePath',
                    'isFile',
                ],
                $from,
            )
        );
    }

    private function checkFromForFinder(): void
    {
        $this->grinWayCommandGetFilesystemServiceForTrait()->throwIfNot(
            [
                'exists',
                'isAbsolutePath',
                'isDir',
            ],
            $this->fromForFinder,
        );
    }

    private function getMaxSteps(): int
    {
        $f = $this->getProgressBarDisplayFrequency();
        if ($f <= 0) {
            $f = 1;
        }

        $maxSteps = \floor($this->quantityConstructedFromToPaths / $f);
        if ($maxSteps <= 0) {
            $maxSteps = 1;
        }

        return $maxSteps;
    }

    private function getDirIfFile(
        string $path,
    ): string {
        if (\is_file($path)) {
            return $this->grinWayCommandGetStringServiceForTrait()->getDirectory($path);
        }
        return $path;
    }

    //###< HELPER ###
}

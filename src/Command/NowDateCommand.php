<?php

namespace GrinWay\Command\Command;

use Symfony\Contracts\Translation\TranslatorInterface;
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
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\{
    InputArgument,
    InputOption,
    InputInterface
};
use Symfony\Component\Console\Output\{
    OutputInterface
};
use GrinWay\Service\Service\{
    BoolService,
    FilesystemService,
    StringService
};

class NowDateCommand extends AbstractCommand
{
    public const DESCRIPTION = 'grin_way_command.command.nowd.description';

    protected ?string $path = null;

    public function __construct(
        $devLogger,
        $t,
        array $progressBarSpin,
        //
        protected readonly StringService $stringService,
        protected readonly FilesystemService $filesystemService,
        protected readonly BoolService $boolService,
        protected $grinWayServiceCarbonFactory,
        protected readonly string $grinWayCommandPathToNircmd,
    ) {
        parent::__construct(
            devLogger:          $devLogger,
            t:                  $t,
            progressBarSpin:    $progressBarSpin,
        );
    }

    protected function configure()
    {
        $this->configureArgument(
            'path',
            InputArgument::REQUIRED,
            $this->t->trans('Путь к файлу, дату которого нужно изменить на текущую дату ПК'),
        );

        parent::configure();
    }

    public function initialize(
        InputInterface $input,
        OutputInterface $output,
    ) {
        parent::initialize(
            $input,
            $output,
        );

        $pathNormalizer = static fn($v) => \trim((string) $v);
        $pathPredicat = function (?string $userArgument, &$argument/*by ref*/) use (&$pathNormalizer): bool {
            $userArgument = $pathNormalizer($userArgument);

            if ($userArgument !== null && empty($userArgument)) {
                $this->exit('Передано пустое значение пути!');
            }

            return $userArgument !== null && $argument != $userArgument;
        };
        $set = static fn(?string $userArgument, &$argument/*by ref*/) => $argument = $pathNormalizer($userArgument);
        $this->initializeArgument(
            $input,
            $output,
            'path',
            $this->path,
            predicat: $pathPredicat,
            set: $set,
        );
    }

    //###> ABSTRACT REALIZATION ###

    /* AbstractCommand */
    protected static function getCommandDescription(): string
    {
        return self::DESCRIPTION;
    }

    /* AbstractCommand */
    protected static function getCommandHelp(): string
    {
        return self::DESCRIPTION;
    }

    /* AbstractCommand */
    protected function command(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $this->path = $this->stringService->makeAbsolute(
            $this->path,
            $this->getRoot(),
        );

        $this->check();

        [
            $splFileInfoSource,
            $oldCTime,
            $oldMTime,
            $formattedCTime,
            $formattedMTime,
        ] = $this->changeDateTime();

        $this->dumpInfoAfterAssignment(
            $input,
            $output,
            $splFileInfoSource,
            $oldCTime,
            $oldMTime,
            $formattedCTime,
            $formattedMTime,
        );

        return Command::SUCCESS;
    }

    //###< ABSTRACT REALIZATION ###


    //###> HELPER ###

    private function changeDateTime(): array
    {
        $splFileInfoSource      = new \SplFileInfo($this->path);
        $oldCTime               = $splFileInfoSource->getCTime();
        $oldMTime               = $splFileInfoSource->getMTime();

        // ###> CHANGE DATE TIME ###
        $timePattern            = 'd-m-Y H:i:s';
        $formattedCTime         = $this->grinWayServiceCarbonFactory->make(new \DateTime())->format($timePattern);
        $formattedMTime         = $formattedCTime; // the same
        $command                = Path::normalize($this->grinWayCommandPathToNircmd);

        if ($this->boolService->isCurrentConsolePathStartsWithSlash()) {
            $pathCd = $this->filesystemService->getLocalRoot();
        } else {
            $pathCd = $this->stringService->getDirectory($this->path);
        }

        //\dd($pathCd);
        $assignCMTimeCommand    = ''
            // ###> TO FORCE WORK WITH ABSOLUTE PATHS ###
            . ' ' . 'cd ' . '"' . $pathCd . '"' . ' &&'
            // ###< TO FORCE WORK WITH ABSOLUTE PATHS ###

            // ###> CHANGE DATE TIME ###
            . ' ' . '"' . $command . '"'
            . ' ' . 'setfiletime'
            . ' ' . '"' . $this->path . '"'
            //created
            . ' ' . '"' . $formattedCTime . '"'
            //modified
            . ' ' . '"' . $formattedMTime . '"'
            // ###< CHANGE DATE TIME ###
        ;
        $resultCode = null;
        \system($assignCMTimeCommand, $resultCode);
        // ###< CHANGE DATE TIME ###

        return [
            $splFileInfoSource,
            $oldCTime,
            $oldMTime,
            $formattedCTime,
            $formattedMTime,
        ];
    }

    private function dumpInfoAfterAssignment(
        InputInterface $input,
        OutputInterface $output,
        \SplFileInfo $splFileInfoSource,
        string $oldCTime,
        string $oldMTime,
        string $formattedCTime,
        string $formattedMTime,
    ): void {

        $CTimeText              = 'Присвоена дата создания: ';
        $MTimeText              = 'Присвоена дата последней модификации: ';

        $currentCTime           = $splFileInfoSource->getCTime();
        $currentMTime           = $splFileInfoSource->getMTime();

        $arrayStrings           = [
            $CTimeText,
            $MTimeText,
            $formattedCTime,
            $formattedMTime,
        ];

        /*
        \dd(
            '$oldCTime ' . $oldCTime,
            '$oldMTime ' . $oldMTime,
            '$currentCTime ' . $currentCTime,
            '$currentMTime ' . $currentMTime,
            '$formattedCTime ' . $formattedCTime,
            '$formattedMTime ' . $formattedMTime,
        );
        */

        $this->getIo()->success([
            $this->stringService->replaceSlashWithSystemDirectorySeparator($this->path),
        ]);

        /*
        php SplFileInfo не видит изменений дат файла, даже после выполенеия изменения

        if ($currentCTime != $oldCTime || $currentMTime != $oldMTime) {
            $this->getIo()->success([
                'Даты файла изменены',
                $this->path,
            ]);
        } else {
            $this->getIo()->warning([
                'Даты файла остались прежними',
                $this->path,
            ]);
        }

        if ($currentCTime != $oldCTime) {
            $output->writeln(
                \str_pad($CTimeText, $this->stringService->getOptimalWidthForStrPad($CTimeText, $arrayStrings))
                . '<bg=yellow;fg=black>' . $formattedCTime . '</>'
                ,
            );
            $this->getIo()->newLine();
        }

        if ($currentMTime != $oldMTime) {
            $output->writeln(
                \str_pad($MTimeText, $this->stringService->getOptimalWidthForStrPad($MTimeText, $arrayStrings))
                . '<bg=yellow;fg=black>' . $formattedMTime . '</>'
                ,
            );
        }
        */

        $output->writeln(
            \str_pad($CTimeText, $this->stringService->getOptimalWidthForStrPad($CTimeText, $arrayStrings))
            . '<bg=yellow;fg=black>' . $formattedCTime . '</>',
        );

        $this->getIo()->newLine();

        $output->writeln(
            \str_pad($MTimeText, $this->stringService->getOptimalWidthForStrPad($MTimeText, $arrayStrings))
            . '<bg=yellow;fg=black>' . $formattedMTime . '</>',
        );
    }

    private function getRoot(): string
    {
        return $this->grinWayCommandInitialCwd;
    }

    private function check(): void
    {
        $this->filesystemService->throwIfNot([
            'exists',
            'isAbsolutePath',
            'isFile',
        ], $this->path);
    }

    //###< HELPER ###
}

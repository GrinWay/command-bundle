<?php

namespace GrinWay\Command\Command;

use function Symfony\Component\String\u;

use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Filesystem\{
    Path,
    Filesystem
};
use Symfony\Component\Finder\Finder;
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
use GrinWay\Service\Service\{
    ConfigService,
    FilesystemService,
    RegexService,
    BufferService,
    DumpInfoService,
    StringService
};
use GrinWay\Command\Contracts\IO\TextIODumper;

class ShowCommand extends AbstractCommand
{
    public const DEPTH = [
        '== 1',/* == 1 ONLY! */
    ];

    public const DESCRIPTION    = 'grin_way_command.command.show.description';

    protected ?string $from     = null;
    protected ?Finder $finder   = null;

    public function __construct(
        $devLogger,
        $t,
        array $progressBarSpin,
        //
        private readonly FilesystemService $filesystemService,
        private readonly StringService $stringService,
        private readonly RegexService $regexService,
    ) {
        parent::__construct(
            devLogger:          $devLogger,
            t:                  $t,
            progressBarSpin:    $progressBarSpin,
        );
    }

    protected function configure()
    {
        $this->configureOption(
            'from',
            description:    $this->t->trans('Откуда сканировать')
                            . ' ' . $this->getDefaultValueForHelp('диск, с наименьшим размером'),
            mode:           InputOption::VALUE_REQUIRED,
            shortcut:       'f',
            add_default_to_description: false,
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

        $this->initializeOption(
            $input,
            $output,
            'from',
            $this->from,
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

        $this->initFrom();

        $this->checkFrom();

        $this->assignFinder();

        $this->make(
            $input,
            $output,
        );

        return Command::SUCCESS;
    }

    //###< ABSTRACT REALIZATION ###


    //###> HELPER ###

    private function make(
        InputInterface $input,
        OutputInterface $output,
    ): void {
        $prevFrom = null;
        foreach ($this->finder as $finderFrom) {
            // check files or dirs in depth 1 (inside 1 dir)
            $from = $this->stringService->replaceSlashWithSystemDirectorySeparator(
                $this->stringService->getDirectory($finderFrom->getRealPath()),
            );
            if ($from === $prevFrom) {
                continue;
            }
            $output->writeln('<bg=white;fg=blue;href=' . $from . '>' . $from . '</>');
            $this->getIo()->newLine();
            $prevFrom = $from;
        }
    }

    private function checkFrom(): void
    {
        $this->filesystemService->throwIfNot(
            [
                'exists',
                'isAbsolutePath',
                'isDir',
            ],
            $this->from,
        );
    }

    private function initFrom(): void
    {
		$driveWord = (string) u($this->t->trans('grin_way_command.command.drive'))->title();
        
		if ($this->from === null) {
			$this->ioDump(
				$driveWord . '?',
				//new TextIODumper,
			);			
		}
		
		$this->from ??= $this->getIo()->ask(
			'->',//$driveWord . '?', // err 
			default: $this->filesystemService->getSmallestDrive(),
        );
		
		$this->from = $this->stringService->getEnsuredRootDrive($this->from);
    }

    private function assignFinder()
    {
        $this->finder = (new Finder())
            ->in($this->from)
            //->files() /* show files and dirs */
            ->ignoreUnreadableDirs()
            ->ignoreDotFiles(true)
            ->sortByName(
                useNaturalSort: true,
            )
            ->notName([
                $this->regexService->getDocxSysFileRegex()
            ])
            ->depth(self::DEPTH)
        ;
    }

    //###< HELPER ###
}

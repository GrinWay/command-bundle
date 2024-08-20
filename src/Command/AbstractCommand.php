<?php

namespace GrinWay\Command\Command;

use function Symfony\Component\String\u;

use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Component\Finder\Finder;
use Monolog\Logger;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Console\Question\{
    Question,
    ConfirmationQuestion
};
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Component\Console\Command\{
    Command,
    LockableTrait,
    SignalableCommandInterface
};
use Symfony\Component\Console\Helper\{
    ProgressBar,
    Table,
    TableStyle,
    TableSeparator
};
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\{
    AsCommand
};
use Symfony\Component\Console\Input\{
    InputInterface
};
use Symfony\Component\Console\Output\{
    OutputInterface
};
use GrinWay\Command\Command\UseTrait\AbstractCommandUseTrait;
use GrinWay\Command\Contracts\IO\AbstractIODumper;
use GrinWay\Command\Contracts\IO\DefaultIODumper;
use GrinWay\Service\Service\BufferService;

// PROJECT_DIR/bin/console <command>
/*
#[AsCommand(
    name: '<>',
    description: '<>',
    hidden: <bool>,
)]
*/
abstract class AbstractCommand extends AbstractCommandUseTrait
{
    use LockableTrait;

    //###> CONSTANTS CHANGE ME ###
    protected bool $makeLock = true;
    protected const WIDTH_PROGRESS_BAR = 40;
    protected const EMPTY_COLOR_PROGRESS_BAR = 'black';
    protected const PROGRESS_COLOR_PROGRESS_BAR = 'bright-blue';
    //###< CONSTANTS CHANGE ME ###

    private TableStyle $_grin_way_command_default_table_style;
    protected $_grin_way_is_display_init_help;
    protected string $_grin_way_command_bundle_config_env_filename = '.env';
    protected string $_grin_way_command_bundle_config_env_local_filename = '.env.local';
    protected ?string $_grin_way_command_bundle_config_path = null;
    protected ?string $_grin_way_command_bundle_config_env_pathname = null;
    protected ?string $_grin_way_command_bundle_config_env_local_pathname = null;

    protected SymfonyStyle $io;
    protected ProgressBar $progressBar;
    protected Table $table;
    protected FormatterHelper $formatter;

    public readonly string $grinWayCommandInitialCwd;

    public function __construct(
		#[Autowire('@monolog.logger.grin_way_command.dev_logger')]
        protected readonly Logger $devLogger,
        protected readonly TranslatorInterface $t,
		#[Autowire('%grin_way_command.progress_bar_spin%')]
        protected readonly array $progressBarSpin,
    ) {
        $this->grinWayCommandInitialCwd = Path::normalize(\getcwd());

        parent::__construct();

        ProgressBar::setPlaceholderFormatterDefinition(
            'spin',
            static function (
                ProgressBar $progressBar,
                OutputInterface $output,
            ) use (&$progressBarSpin) {
                static $i = 0;
                if ($i >= \count($progressBarSpin)) {
                    $i = 0;
                }
                return $progressBarSpin[$i++];
            }
        );

        ProgressBar::setFormatDefinition('normal', '%bar% %percent:2s%% %spin%');
        ProgressBar::setFormatDefinition('normal_nomax', '%bar% progress: %current% %spin%');
    }

    #[Required]
    public function _grinWayCommandSetRequired(
        #[Autowire(value: '%grin_way_command.display_init_help%')]
        bool $displayInitHelp,
        #[Autowire(value: '%kernel.project_dir%')]
        string $kernelProjectDir,
        #[Autowire('@GrinWay\Service\Service\StringService')]
        $stringService,
    ): void {
        $this->_grin_way_command_bundle_config_path = $stringService->replaceSlashWithSystemDirectorySeparator(
            $kernelProjectDir,
        );
        $this->_grin_way_command_bundle_config_env_pathname = $stringService->replaceSlashWithSystemDirectorySeparator(
            $stringService->getPath(
				$kernelProjectDir,
				$this->_grin_way_command_bundle_config_env_filename,
			),
        );
        $this->_grin_way_command_bundle_config_env_local_pathname = $stringService->replaceSlashWithSystemDirectorySeparator(
            $stringService->getPath(
				$kernelProjectDir,
				$this->_grin_way_command_bundle_config_env_local_filename,
			),
        );
        $this->_grin_way_is_display_init_help = $displayInitHelp;
    }


    //###> ABSTRACT ###

    /* AbstractCommand */
    abstract protected static function getCommandDescription(): string;

    /* AbstractCommand */
    abstract protected static function getCommandHelp(): string;

    //###< ABSTRACT ###


    //###> PUBLIC API ###

	/*
		Use it instead of $this->getIo()->note(), ...
	*/
    public function ioDump(
		mixed $message,
		?AbstractIODumper $dumper = null,
		int $afterDumpNewLines = 0,
		bool $translate = true,
	): static {
		$dumper ??= new DefaultIODumper;
		
		if ($translate && (false
			|| \is_string($message)
			|| \is_int($message)
			|| \is_float($message)
			|| \is_null($message)
		)) {
			$message = $this->getTranslator()->trans(\is_null($message) ? $message : (string) $message);
		}
		
		$dumper(
			$this->getIo(),
			$message,
		);
		
		while ($afterDumpNewLines-- > 0) {
			$this->getIo()->writeln('');
		}
		
        return $this;
    }

	/**/
    public function &getTranslator(): TranslatorInterface
    {
        return $this->t;
    }

	/**/
    public function &getIo(): SymfonyStyle
    {
        return $this->io;
    }

	/**/
    public function &getProgressBar(): ProgressBar
    {
        return $this->progressBar;
    }

	/**/
    public function &getTable(): Table
    {
        return $this->table;
    }

	/**/
    public function &getDefaultTableStyle(): TableStyle
    {
        return $this->_grin_way_command_default_table_style;
    }

	/**/
    public function getCloneTable(): Table
    {
        return clone $this->table;
    }

	/**/
    public function &getFormatter(): FormatterHelper
    {
        return $this->formatter;
    }

    //###< PUBLIC API ###


    //###> API ###

    /*
        Debug: Symfony Finder
        ONLY FOR DEBUGGING
    */
    protected function ddFinder(
        Finder $finder,
        bool $isExit = true,
    ): void {
        $this->io->warning('FINDER START');

        foreach ($finder as $finderSplFileInfo) {
            $this->io->info(
                Path::normalize($finderSplFileInfo->getRealPath()),
            );
        }

		$exitMess = 'FINDER END';
        if ($isExit) {
			$this->exit($exitMess);
		} else {
			$this->io->warning($exitMess);			
		}
    }

    /*
        Debug: Symfony \dd()
        ONLY FOR DEBUGGING
    */
    protected function dd(
        ...$forDDPack,
    ): void {
		$this->io->warning('\\dd() START');
		
        foreach($forDDPack as $k => $forDD) {
			if ($forDD instanceof Finder) {
				$this->ddFinder($forDD, isExit: false);
				unset($forDDPack[$k]);
				continue;
			}
		}
		
		$exitMess = '\\dd() END';
		if (!empty($forDDPack)) {
			\dd(
				$forDDPack,
				'SYSTEM MESSAGE: ' . $exitMess,
			);				
		}
		
		$this->exit($exitMess);
    }

    /*
        Usage:

        protected function configure(): void
        {
            $this->METHOD_NAME(
                name:           NAME,
                default:        PROPERTY,
                description:    DESCRIPTION,
                mode:           InputOption::<CONSTANT>,
                shortcut:       SHORTCUT,
            );
        }
    */
    protected function configureOption(
        string $name,
        string $description,
        int $mode,
        mixed $default = null,
        string|array $shortcut = null,
        bool $add_default_to_description = true,
    ): void {
        if ($add_default_to_description && $mode != InputOption::VALUE_REQUIRED) {
            $description = $this->getInfoDescription($mode, $description, $default);
        }

        if ($shortcut === null) {
            $this
                ->addOption(
                    name:           $name,
                    mode:           $mode,
                    description:    $description,
                    default:        $default,
                )
            ;
            return;
        }

        $this
            ->addOption(
                name:           $name,
                shortcut:       $shortcut,
                mode:           $mode,
                description:    $description,
                default:        $default,
            )
        ;
    }

    /*
        Usage:

        protected function configure(): void
        {
            $this->METHOD_NAME(
                name:           NAME,
                mode:           InputArgument::CONSTANT,
                description:    DESCRIPTION,
            );
        }
    */
    protected function configureArgument(
        string $name,
        int $mode,
        ?string $description = null,
        mixed $default = null,
        bool $add_default_to_description = true,
    ) {
        if ($add_default_to_description && $description !== null && $default !== null) {
            $description = $this->getInfoDescription($mode, $description, $default);
        }

        if ($description === null) {
            $this
                ->addArgument(
                    $name,
                    $mode,
                )
            ;
            return;
        }

        $this
            ->addArgument(
                $name,
                $mode,
                $description,
            )
        ;
    }

    /*
        Usage:

        protected function initialize(): void
        {
            $this->METHOD_NAME(
                input:          InputInterface,
                output:         OutputInterface,
                name:           NAME,
                option:         $this->OPTION,
                predicat:       CALLABLE,
                set:            CALLABLE,
            );
        }
    */
    protected function initializeOption(
        InputInterface $input,
        OutputInterface $output,
        string $name,
        &$option,
        \Closure|\callable|null $predicat = null,
        \Closure|\callable|null $set = null,
    ) {
        /* $userOption always string, != more suitable */
        $predicat ??= static fn(?string $userOption, &$option/*by ref*/)
            => $userOption !== null && $option != $userOption;

        $set ??= static fn(?string $userOption, &$option/*by ref*/) => $option = $userOption;

        $userOption = $input->getOption($name);
        if ($predicat(userOption: $userOption, option: $option)) {
            $set(userOption: $userOption, option: $option);
        }
    }


    /*
        Usage:

        protected function initialize(): void
        {
            $this->METHOD_NAME(
                input:          InputInterface,
                output:         OutputInterface,
                name:           NAME,
                argument:       $this->ARGUMENT,
                predicat:       CALLABLE,
                set:            CALLABLE,
            );
        }
    */
    protected function initializeArgument(
        InputInterface $input,
        OutputInterface $output,
        string $name,
        &$argument,
        \Closure|\callable $predicat = null,
        \Closure|\callable $set = null,
    ) {
        /* $userArgument always string, != more suitable */
        $predicat ??= static fn(?string $userArgument, &$argument/*by ref*/)
            => $userArgument !== null && $argument != $userArgument;

        $set ??= static fn(?string $userArgument, &$argument/*by ref*/) => $argument = $userArgument;

        $userArgument = $input->getArgument($name);
        if ($predicat(userArgument: $userArgument, argument: $argument)) {
            $set(userArgument: $userArgument, argument: $argument);
        }
    }

    /*
        Gets the TRANSLATED description of the option or argument configuration
    */
    protected function getInfoDescription(
        int $mode,
        string $description,
        mixed $default,
    ): string {
        $description = $this->t->trans($description);

        if ($mode === InputOption::VALUE_NEGATABLE && gettype($default) === 'boolean') {
            return (string) u(
                ''
                    . u($description)->ensureEnd(' ')
                    . $this->getDefaultValueNegatableForHelp($default)
            )->collapseWhitespace();
        }

        return (string) u(
            ''
                . u($description)->ensureEnd(' ')
                . $this->getDefaultValueForHelp($default)
        )->collapseWhitespace();
    }

    /*
        Gets part of the option description
    */
    protected function getDefaultValueForHelp(
        ?string $default,
    ): string {
        if ($default === null) {
            return '';
        }
        return '<bg=black;fg=yellow>[default: "' . $default . '"]</>';
    }

    /*
        Gets boolean part of the option description
    */
    protected function getDefaultValueNegatableForHelp(
        ?bool $bool,
    ): string {
        if ($bool === null) {
            return '';
        }
        return $this->getDefaultValueForHelp($bool ? 'yes' : 'no');
    }

    /*
        Asks user in the console: MOVE ON?
		
		Usage:
			if ($this->isOk()) {
				//...
			}
    */
    protected function isOk(
        array|string $message = 'grin_way_command.command.default.is_ok',
        bool $default = true,
        bool $exitWhenDisagree = false,
    ) {
        $message = $this->t->trans($message);

        $agree = $this->io->askQuestion(
            new ConfirmationQuestion(
                \is_array($message) ? \implode(\PHP_EOL, $message) : $message,
                $default,
            )
        );

        if ($exitWhenDisagree && !$agree) {
            $this->exit('grin_way_command.command.exit_disagree');
        }

        return $agree;
    }

    /*
        Dumps the TRANSLATED message

        Executes callback

        And afther all this exits from the command
    */
    protected function exit(
        array|string|null $message = null,
        \Closure|callable|null $callback = null,
		string $style = 'warning',
    ) {
        //$this->devLogger->info(__METHOD__);
		
		if ($style === 'warning') {
			$showMessCallback = $this->io->warning(...);
		} else if ($style === 'error') {
			$showMessCallback = $this->io->error(...);
		}

        //###> message
        $this->io->writeln('');
        if ($message !== null) {
            $showMessCallback(
                $this->t->trans($message),
            );
        } else {
            $showMessCallback(
                $this->t->trans('grin_way_command.command.default.exit'),
            );
        }

        //###> callback before the exit
        if (!\is_null($callback)) {
            $callback();
        }

        //###> the exit
        exit(Command::INVALID);
    }

    /*
        Alias for exit with the defined message
    */
    protected function shutdown(): void
    {
        $this->exit(
            $this->t->trans(
                'grin_way_command.command.shutdown',
                parameters: [
                    '%command%' => $this->getName(),
                ],
            )
        );
    }

    //###< API ###


    //###> ABSTRACT REALIZATION ###

    /* AbstractGetCommandTrait
        Get This Command into service and use API of this Command
    */
    protected function &grinWayCommandGetCommandForTrait(): AbstractCommand
    {
        return $this;
    }

    /* MakeLockAbleTrait */
    protected function &getMakeLockProperty(): bool
    {
        return $this->makeLock;
    }

    /* AbstractCommandTrait */
    protected function configure()
    {
        //\pcntl_signal(\SIGINT, $this->shutdown(...));
        //\register_shutdown_function($this->shutdown(...));

        $this->configureCommandHelp();

        $this->configureCommandDescription();

        $this->configureLockOption();

        $this->configureOption(
            name:           'grin-way-command-display-init-help',
            mode:           InputOption::VALUE_NEGATABLE,
            description:    $this->t->trans('grin_way_command.init_help.description_of_flag'),
        );

        /*###> parent::configure() AT THE END ###*/
        parent::configure();
    }

    /* AbstractCommandTrait */
    protected function initialize(
        InputInterface $input,
        OutputInterface $output,
    ) {
        /*###> parent::initialize() AT THE BEGINNING ###*/
        parent::initialize(
            $input,
            $output,
        );

        $this->initializeOption(
            $input,
            $output,
            'grin-way-command-display-init-help',
            $this->_grin_way_is_display_init_help,
        );

        //###>
        $this->io = new SymfonyStyle($input, $output);
        $this->setFormatter(
            $input,
            $output,
        );
        $this->setProgressBar(
            $input,
            $output,
        );
        $this->setDefaultTableStyle(
            $input,
            $output,
        );
        $this->setTable(
            $input,
            $output,
        );

        $this->initializeLockOption(
            $input,
            $output,
        );
    }

    /* AbstractCommand
        // OK
        return Command::SUCCESS;

        // Incorrect usage
        return Command::INVALID;

        // Program failure
        return Command::FAILURE;
    */
    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
		//###> BEFORE LOCK
		$this->executeBeforeLock(
			$input,
			$output,
		);
		
        //###> LOCK
        if ($this->getMakeLockProperty()) {
            if (
                !$this->lock(
                    $this->getLockName(),
                )
            ) {
                $this->exit(
                    $this->getExitCuzLockMessage(),
                );
                return Command::FAILURE;
            }
        }

        $this->displayInitHelp(
            $input,
            $output,
        );

        $code = $this->command(
            $input,
            $output,
        );

        return $code;
    }

    /* Command */
    protected function interact(
        InputInterface $input,
        OutputInterface $output,
    ) {
        // get missed options/arguments
    }

    /* SignalableCommandInterface */
    public function getSubscribedSignals(): array
    {
        return [
            //\SIGINT,
            //\SIGTERM,
        ];
    }

    /* SignalableCommandInterface */
    public function handleSignal(int $signal, int|false $previousExitCode = 0): int|false
    {
        /*
        if (\SIGINT == $signal) {
            $this->shutdown();
        }
        */
    }

    /* ServiceSubscriberInterface */
    public static function getSubscribedServices(): array
    {
        return [
            'logger' => '?Psr\Log\LoggerInterface',
        ];
    }

    //###< ABSTRACT REALIZATION ###


    //###> YOU CAN OVERRIDE IT  ###
	
    /* AbstractCommand */
	protected function getHeaderInitHelpMessages(): array {
		return [
			$this->t->trans(
				'grin_way_command.init_help.init_description',
			),
		];
	}
	
    /* AbstractCommand
		If the key !is_int() doesn't number it
	*/
	protected function getBodyInitHelpMessages(): array {
		return [
			$this->t->trans(
				'grin_way_command.init_help.exit_shortcut',
			),
		];
	}
	
    /* AbstractCommand */
	protected function getBottomInitHelpMessages(): array {
		return [
			$this->t->trans(
				'grin_way_command.init_help.how_to_change_lang',
				[
					'%bundle_config_path%' => $this->_grin_way_command_bundle_config_path,
					'%env_local_filename%' => $this->_grin_way_command_bundle_config_env_local_filename,
					'%lang_example%' => 'LOCALE="en_US"',
				],
			),
			$this->t->trans(
				'grin_way_command.init_help.how_to_change_default_behaviour',
				[
					'%env_pathname%' => $this->_grin_way_command_bundle_config_env_pathname,
					'%env_local_pathname%' => $this->_grin_way_command_bundle_config_env_local_pathname,
				],
			),
			$this->t->trans(
				'grin_way_command.init_help.i_want_to_remove_init_description',
				[
					'%bundle_config_path%' => $this->_grin_way_command_bundle_config_path,
					'%env_local_filename%' => $this->_grin_way_command_bundle_config_env_local_filename,
				],
			),
		];
	}	
	
    /* AbstractCommand */

    /* AbstractCommand */
    protected function executeBeforeLock(
        InputInterface $input,
        OutputInterface $output,
    ): void {
		// nothing by default
	}

    /* AbstractCommand */
    protected function displayInitHelp(
        InputInterface $input,
        OutputInterface $output,
    ): void {
        if ($this->_grin_way_is_display_init_help) {
            $this->getIo()->warning($this->getInitHelpMessages());
        }
    }

    protected function getExitCuzLockMessage(): string
    {
        return ''
            . $this->t->trans('grin_way_command.command_word')
            . ' ' . '"' . $this->getName() . '" '
            . $this->t->trans('grin_way_command.already_triggered') . '!'
        ;
    }

    /* AbstractCommand */
    protected function getLockName(): string
    {
        return $this->getName();
    }

    /* AbstractCommand */
    protected function setFormatter(
        InputInterface $input,
        OutputInterface $output,
    ): void {
		$this->setHelperSet(new HelperSet([
			'formatter' => new FormatterHelper(),
		]));
        $this->formatter = $this->getHelper('formatter');
    }

    /* AbstractCommand
        WHEN YOU HAVE THE MAX STEPS USE IT:
            $this->progressBar->setMaxSteps(<int>);
            $this->progressBar->start();
    */
    protected function setProgressBar(
        InputInterface $input,
        OutputInterface $output,
    ): void {
        $this->progressBar = $this->io->createProgressBar();
        $this->progressBar->setEmptyBarCharacter("<bg=" . static::EMPTY_COLOR_PROGRESS_BAR . "> </>");
        $this->progressBar->setProgressCharacter("<bg=" . static::EMPTY_COLOR_PROGRESS_BAR . ";fg=" . static::EMPTY_COLOR_PROGRESS_BAR . "> </>");
        $this->progressBar->setBarCharacter("<bg=" . static::PROGRESS_COLOR_PROGRESS_BAR . "> </>");
        $this->progressBar->setBarWidth(static::WIDTH_PROGRESS_BAR);
    }

    /* AbstractCommand */
    protected function setDefaultTableStyle(
        InputInterface $input,
        OutputInterface $output,
    ): void {
        $tableStyle = new TableStyle();

        //###> customize style
        $tableStyle
            ->setHorizontalBorderChars(' ')
            ->setVerticalBorderChars(' ')
            ->setDefaultCrossingChar(' ')
        ;

        //###> set style
        $this->_grin_way_command_default_table_style = $tableStyle;
    }

    /* AbstractCommand */
    protected function setTable(
        InputInterface $input,
        OutputInterface $output,
    ): void {
        //###> create table
        $table = new Table($output); //$this->io->createTable();

        //###> set style
        $table->setStyle($this->getDefaultTableStyle());
		
		$this->table = $table;
    }

    //###< YOU CAN OVERRIDE IT ###


    //###> HELPER ###
	
	private function getInitHelpMessages(): array {
		$n = 0;
		$numberedBodyMessages = $this->getBodyInitHelpMessages();
		\array_walk(
			$numberedBodyMessages,
			static function(&$v, $k) use (&$n) {
				$v = is_int($k) ? ((string) u($v)->ensureStart(++$n . ') ')) : $v;
			},
		);
		
		return [
			...$this->getHeaderInitHelpMessages(),
			...$numberedBodyMessages,
			...$this->getBottomInitHelpMessages(),
		];
	}

    protected function configureCommandHelp(): void
    {
        $this
            ->setHelp(
                $this->t->trans(
                    static::getCommandHelp(),
                ),
            )
        ;
    }

    protected function configureCommandDescription(): void
    {
        $this
            ->setDescription(
                $this->t->trans(
                    static::getCommandDescription(),
                ),
            )
        ;
    }

    //###< HELPER ###
}

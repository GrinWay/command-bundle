<?php

namespace GrinWay\Command\Command;

use function Symfony\Component\String\u;

use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\OptionsResolver\OptionsResolver;
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
use Symfony\Component\Console\Question\{
    ConfirmationQuestion,
    Question
};
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
use Symfony\Component\Console\fromatter\OutputfromatterStyle;
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
    DumpInfoService,
    RegexService,
    StringService,
    FilesystemService
};
use GrinWay\Command\Trait\AbstractPdfCommandTrait;
use GrinWay\Command\Command\UseTrait\AbstractConvertExtCommandUseTrait;

class PdfCommand extends AbstractConvertExtCommandUseTrait
{
    public const DESCRIPTION = 'grin_way_command.command.pdf.description';

    protected bool $dumpConvertedInfo = true;
    protected bool $move            = false;
    protected array|string $depth   = '== 0';
    protected bool $ask             = true;
    protected bool $dumpInfo        = true;
    protected bool $override        = false;
    protected bool $askOverride     = true;

    public function __construct(
        $devLogger,
        $t,
        array $progressBarSpin,
        //
        protected readonly StringService $stringService,
        protected readonly FilesystemService $filesystemService,
        protected readonly DumpInfoService $dumpInfoService,
        protected readonly RegexService $regexService,
        //
        protected readonly string $grinWayCommandPathToPdfConverter,
    ) {
        parent::__construct(
            devLogger:          $devLogger,
            t:                  $t,
            progressBarSpin:    $progressBarSpin,
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

    /* AbstractConvertExtCommandTrait */
    protected function &grinWayCommandGetStringServiceForTrait(): StringService
    {
        return $this->stringService;
    }

    /* AbstractConvertExtCommandTrait */
    protected function &grinWayCommandGetFilesystemServiceForTrait(): FilesystemService
    {
        return $this->filesystemService;
    }

    /* AbstractConvertExtCommandTrait */
    protected function &grinWayCommandGetDumpInfoServiceForTrait(): DumpInfoService
    {
        return $this->dumpInfoService;
    }

    /* AbstractConvertExtCommandTrait */
    protected function &grinWayCommandGetRegexServiceForTrait(): RegexService
    {
        return $this->regexService;
    }

    /* MoveAbleTrait */
    protected function &getMoveProperty(): bool
    {
        return $this->move;
    }

    /* DepthAbleTrait */
    protected function &getDepthProperty(): array|string
    {
        return $this->depth;
    }

    /* AskAbleTrait */
    protected function &getAskProperty(): bool
    {
        return $this->ask;
    }

    /* DumpInfoAbleTrait */
    protected function &getDumpInfoProperty(): bool
    {
        return $this->dumpInfo;
    }

    /* OverrideAbleTrait */
    protected function &getOverrideProperty(): bool
    {
        return $this->override;
    }

    /* OverrideAbleTrait */
    protected function &isAskOverride(): bool
    {
        return $this->askOverride;
    }

    /* AbstractConvertExtCommandTrait */
    protected function &isDumpConvertedInfo(): bool
    {
        return $this->dumpConvertedInfo;
    }

    protected function getFromExtensions(): string|array
    {
        return [
            'doc',
            'docx',
        ];
    }

    protected function getToExtension(): string
    {
        return 'pdf';
    }

    protected function getFromDescription(): string
    {
        return 'Откуда брать .doc и .docx документы (может быть как папкой, так и файлом).';
    }

    protected function getToDescription(): string
    {
        return 'Куда сохранять результат (может быть как папкой, так и файлом).';
    }

    protected function getDefaultFrom(): string
    {
        return $this->grinWayCommandInitialCwd;
    }

    protected function getDefaultTo(): string
    {
        return $this->getDefaultFrom();
    }

    protected function saveConvertedTo(
        string $absPathFrom,
        string $absPathTo,
    ): void {
        $code = null;
        $commandPath = $this->grinWayCommandPathToPdfConverter;
        $filesystemService = $this->filesystemService;

        $command = ''
            . '"' . $commandPath . '"'
            . ' -f "' . $absPathFrom . '"'
            . ' -O "' . $absPathTo . '"'
            . ' -T "wdFormatPDF"'
        ;
        try {
            \chdir($filesystemService->getLocalRoot());
            \exec($command, result_code: $code);
        } finally {
            \chdir($this->grinWayCommandInitialCwd); /* ! RETURN TO THE CURRENT DIRECTORY ! */
            if (!\is_null($code) && $code !== Command::SUCCESS) {
                $this->exit(
                    message: 'Отмена',
                    callback: static fn() => $filesystemService->deleteByAbsPathIfExists($absPathTo),
                );
            }
        }
    }

    //###< ABSTRACT REALIZATION ###
}

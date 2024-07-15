<?php

namespace GrinWay\Command\Contracts\IO;

use Symfony\Component\Console\Style\SymfonyStyle;
use GrinWay\Command\Command\AbstractCommand;

class FormattedIODumper extends AbstractIODumper
{
	protected readonly string $format;

	public function __construct(
		?string $format = null,
		int $afterDumpNewLines = 0,
	) {
		parent::__construct(
			afterDumpNewLines: $afterDumpNewLines,
		);
		
		//###>
		$format ??= '<bg=black;fg=green>%s</>';
		$this->format = \trim($format);
	}
	
	//###> API ###
	
	/*
		Gets colored string
	*/
	protected function getFormatted(
		$string,
	): string {
		if (empty($this->format)) {
			return $string;
		}
		
		return \sprintf($this->format, $string);
	}
	
	//###< API ###

	
	//###> ABSTRACT ###
	
	/* AbstractIODumper */
	protected function dump(
		SymfonyStyle &$io,
		mixed $normalizedMessage,
	): void {
		$io->text($normalizedMessage);
	}
	
	//###< ABSTRACT ###
	
	
	//###> CAN OVERRIDE ###
	
	/* AbstractIODumper */
	protected function getNormalizedMessage(
		mixed $message,
	): mixed {
		$getFormatted = $this->getFormatted(...);
		
		if (\is_array($message)) {
			\array_walk(
				$message,
				static fn(&$el) => $el = $getFormatted($el),
			);
		} else {
			$message = $getFormatted($message);
		}
		
		return $message;
	}
	
	//###< CAN OVERRIDE ###
}

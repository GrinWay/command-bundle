<?php

namespace GrinWay\Command\Contracts\IO;

use Symfony\Component\Console\Style\SymfonyStyle;

class TitleIODumper extends AbstractIODumper
{
	//###> ABSTRACT ###
	
	/* AbstractIODumper */
	protected function dump(
		SymfonyStyle &$io,
		mixed $normalizedMessage,
	): void {
		$io->title($normalizedMessage);
	}
	
	//###< ABSTRACT ###
	
	
	//###> CAN OVERRIDE ###
	
	/* AbstractIODumper */
	protected function getNormalizedMessage(
		mixed $message,
	): string {
		return (string) $message;
	}
	
	/* AbstractIODumper */
	protected function isSkip(
		mixed $message,
	): bool {
		if (parent::isSkip($message)) {
			return true;
		}
		
		if (\is_array($message)) {
			return true;
		}
		
		return false;
	}
	
	//###< CAN OVERRIDE ###
}

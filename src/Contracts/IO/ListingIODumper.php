<?php

namespace GrinWay\Command\Contracts\IO;

use Symfony\Component\Console\Style\SymfonyStyle;

class ListingIODumper extends AbstractIODumper
{
	//###> ABSTRACT ###
	
	/* AbstractIODumper */
	protected function dump(
		SymfonyStyle &$io,
		mixed $normalizedMessage,
	): void {
		$io->listing($normalizedMessage);
	}
	
	//###< ABSTRACT ###
	
	
	//###> CAN OVERRIDE ###
	
	/* AbstractIODumper */
	protected function getNormalizedMessage(
		mixed $message,
	): array {
		return \is_array($message) ? $message : [$message];
	}
	
	//###< CAN OVERRIDE ###
}

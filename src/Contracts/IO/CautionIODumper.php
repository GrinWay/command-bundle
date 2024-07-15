<?php

namespace GrinWay\Command\Contracts\IO;

use Symfony\Component\Console\Style\SymfonyStyle;

class CautionIODumper extends AbstractIODumper
{
	//###> ABSTRACT ###
	
	/* AbstractIODumper */
	protected function dump(
		SymfonyStyle &$io,
		mixed $normalizedMessage,
	): void {
		$io->caution($normalizedMessage);
	}
	
	//###< ABSTRACT ###
}

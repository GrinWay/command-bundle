<?php

namespace GrinWay\Command\Contracts\IO;

use Symfony\Component\Console\Style\SymfonyStyle;

class CommentIODumper extends AbstractIODumper
{
	//###> ABSTRACT ###
	
	/* AbstractIODumper */
	protected function dump(
		SymfonyStyle &$io,
		mixed $normalizedMessage,
	): void {
		$io->comment($normalizedMessage);
	}
	
	//###< ABSTRACT ###
}

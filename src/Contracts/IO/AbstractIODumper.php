<?php

namespace GrinWay\Command\Contracts\IO;

use Symfony\Component\Console\Style\SymfonyStyle;
use GrinWay\Service\Service\BufferService;

abstract class AbstractIODumper
{
	public function __construct(
		protected readonly int $afterDumpNewLines = 0,
	) {
	}
	
	public function __invoke(
		SymfonyStyle &$io,
		mixed $message,
		bool $flush = false,
	): static {
		if ($this->isSkip($message)) {
			return $this;
		}
		
		if (\is_array($message)) {
			foreach($message as $m) {
				$this->dump(
					$io,
					$this->getNormalizedMessage($m),
				);
			}
		} else {
			$this->dump(
				$io,
				$this->getNormalizedMessage($message),
			);			
		}
		
		$afterDumpNewLines = $this->afterDumpNewLines;
		while(0 < $afterDumpNewLines--) {
			$io->writeln('');
		}
		
		if ($flush) {
			BufferService::clear();
		}
		
		return $this;
	}
	
	//###> ABSTRACT ###
	
	/* AbstractIODumper
		Must dump the message with the SymfonyStyle object
		(order 3)
	*/
	abstract protected function dump(
		SymfonyStyle &$io,
		mixed $normalizedMessage,
	): void;
	
	//###< ABSTRACT ###
	
	
	//###> CAN OVERRIDE ###
	
	/* Returns array|string (by default)
		(order 2)
	*/
	/* AbstractIODumper */
	protected function getNormalizedMessage(
		mixed $message,
	): mixed {
		return \is_array($message) ? $message : (string) $message;
	}
	
	/* (order 1) */
	/* Usage:		
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
	*/
	/* AbstractIODumper */
	protected function isSkip(
		mixed $message,
	): bool {
		if (\is_null($message)) {
			return true;
		}
		
		if (true
			//###> ALL THE ALLOWED TYPES ###
			&& !\is_string($message)
			&& !\is_int($message)
			&& !\is_float($message)
			&& !\is_array($message)
			//###< ALL THE ALLOWED TYPES ###
		) {
			return true;
		}
		
		return false;
	}
	
	//###< CAN OVERRIDE ###
}

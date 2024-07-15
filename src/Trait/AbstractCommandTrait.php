<?php

namespace GrinWay\Command\Trait;

use GrinWay\Command\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/*
    configure -> initialize -> execute -> command
*/
trait AbstractCommandTrait
{
    //###> ABSTRACT ###

    /* AbstractCommandTrait
        FOR USER
    */
    abstract protected function command(
        InputInterface $input,
        OutputInterface $output,
    ): int;


    /* Command */
    abstract protected function configure();

    /* Command */
    abstract protected function initialize(
        InputInterface $input,
        OutputInterface $output,
    );

    /* Command */
    abstract protected function execute(
        InputInterface $input,
        OutputInterface $output,
    );

    //###< ABSTRACT ###
}

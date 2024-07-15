<?php

namespace GrinWay\Command\Trait;

use GrinWay\Command\Command\AbstractCommand;

trait AbstractGetCommandTrait
{
    //###> ABSTRACT ###

    /* AbstractGetCommandTrait
        Get This Command into service and use API of this Command
    */
    abstract protected function &grinWayCommandGetCommandForTrait(): AbstractCommand;

    //###< ABSTRACT ###
}

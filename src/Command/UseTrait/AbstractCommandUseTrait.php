<?php

namespace GrinWay\Command\Command\UseTrait;

use Symfony\Component\Console\Command\{
    Command,
    SignalableCommandInterface
};
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use GrinWay\Command\Trait\AbstractCommandTrait;
use GrinWay\Command\Trait\MakeLockAbleTrait;

abstract class AbstractCommandUseTrait extends Command implements
    SignalableCommandInterface,
    ServiceSubscriberInterface
{
    use AbstractCommandTrait;
    use MakeLockAbleTrait;
}

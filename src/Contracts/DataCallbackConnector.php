<?php

namespace GrinWay\Command\Contracts;

class DataCallbackConnector
{
    protected $callback;

    public function __construct(
        protected readonly mixed $data,
        callable|\Closure $callback,
    ) {
        $this->callback = $callback;
    }

    public function __invoke()
    {
        return ($this->callback)($this->data);
    }
}

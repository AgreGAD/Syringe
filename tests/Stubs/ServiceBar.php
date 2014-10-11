<?php

namespace Butterfly\Component\DI\Tests\Stubs;

class ServiceBar implements IServiceFooAware
{
    protected $internalService;

    public function injectServiceFoo(ServiceFoo $service)
    {
        $this->internalService = $service;
    }

    public function getInternalService()
    {
        return $this->internalService;
    }
}
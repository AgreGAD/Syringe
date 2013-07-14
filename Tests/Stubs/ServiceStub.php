<?php

namespace Syringe\Tests\Stubs;

class ServiceStub
{
    public $a;

    protected $b;
    protected $c;

    public function __construct($b = null, $c = null)
    {
        $this->b = $b;
        $this->c = $c;
    }

    public function setB($b)
    {
        $this->b = $b;
    }

    public function getB()
    {
        return $this->b;
    }

    public function setC($c)
    {
        $this->c = $c;
    }

    public function getC()
    {
        return $this->c;
    }
}

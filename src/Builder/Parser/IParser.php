<?php

namespace Butterfly\Component\DI\Builder\Parser;

interface IParser
{
    /**
     * @param string $file
     * @return array
     */
    public function parse($file);
}
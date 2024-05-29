<?php

namespace FpDbTest\Nodes;

abstract class AbstractNode
{
    public function __construct(protected mixed $value)
    {
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

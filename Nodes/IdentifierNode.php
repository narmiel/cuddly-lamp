<?php

namespace FpDbTest\Nodes;

class IdentifierNode extends AbstractNode
{
    public function __toString(): string
    {
        return '`' . $this->value . '`';
    }
}

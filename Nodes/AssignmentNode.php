<?php

namespace FpDbTest\Nodes;

class AssignmentNode extends AbstractNode
{
    public function __construct(IdentifierNode $key, mixed $value)
    {
        parent::__construct([$key, $value]);
    }

    public function __toString(): string
    {
        return $this->value[0] . ' = ' . $this->value[1];
    }
}

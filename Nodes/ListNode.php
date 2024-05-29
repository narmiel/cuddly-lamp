<?php

namespace FpDbTest\Nodes;

class ListNode extends AbstractNode
{
    public function __toString(): string
    {
        return implode(', ', $this->value);
    }
}

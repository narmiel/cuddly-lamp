<?php

namespace FpDbTest\Nodes;

class BlockNode extends AbstractNode
{
    public function __construct()
    {
        parent::__construct([]);
    }

    public function addNode(AbstractNode $node): void
    {
        $this->value[] = $node;
    }

    public function __toString(): string
    {
        foreach ($this->value as $node) {
            if ($node instanceof SkipNode) {
                return '';
            }
        }

        return implode('', $this->value);
    }
}

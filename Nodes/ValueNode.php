<?php

namespace FpDbTest\Nodes;

use FpDbTest\Enums\ValueNodeTypeEnum;

class ValueNode extends AbstractNode
{
    public function __construct(protected mixed $value, protected ValueNodeTypeEnum $type)
    {
        if ($type === ValueNodeTypeEnum::Int || $type === ValueNodeTypeEnum::Float) {
            if (is_null($value)) {
                $this->type = ValueNodeTypeEnum::Null;
            }
        }

        parent::__construct($value);
    }

    public function __toString(): string
    {
        return match ($this->type) {
            ValueNodeTypeEnum::Int, ValueNodeTypeEnum::Bool => (int)$this->value,
            ValueNodeTypeEnum::Float => (float)$this->value,
            ValueNodeTypeEnum::Id => '`' . $this->value . '`',
            ValueNodeTypeEnum::Null => 'NULL',
            ValueNodeTypeEnum::String => '\'' . $this->value . '\'',
        };
    }
}

<?php

namespace FpDbTest;

use FpDbTest\Enums\TokenEnum;
use FpDbTest\Enums\ValueNodeTypeEnum;
use FpDbTest\Nodes\AbstractNode;
use FpDbTest\Nodes\AssignmentNode;
use FpDbTest\Nodes\BlockNode;
use FpDbTest\Nodes\IdentifierNode;
use FpDbTest\Nodes\ListNode;
use FpDbTest\Nodes\LiteralNode;
use FpDbTest\Nodes\SkipNode;
use FpDbTest\Nodes\ValueNode;
use InvalidArgumentException;

class Parser
{
    protected int $argIndex = 0;
    protected int $stack = 0;

    public function __construct(protected array $args = [])
    {
    }

    public function parse(string $query): BlockNode
    {
        $tokens = Parser::tokenize($query);

        $astStack[$this->stack] = new BlockNode();

        foreach ($tokens as $token) {
            $tokenEnum = TokenEnum::tryFrom($token);

            if ($tokenEnum === null) {
                $astStack[$this->stack]->addNode(new LiteralNode($token));
                continue;
            }

            if ($tokenEnum === TokenEnum::OpenBrace) {
                if ($this->stack !== 0) {
                    throw new InvalidArgumentException('Unexpected open brace');
                }

                $newBlock = new BlockNode();
                $astStack[$this->stack]->addNode($newBlock);
                $astStack[++$this->stack] = $newBlock;
                continue;
            }

            if ($tokenEnum === TokenEnum::CloseBrace) {
                if ($this->stack !== 1) {
                    throw new InvalidArgumentException('Unexpected close brace');
                }

                $this->stack--;
                continue;
            }

            if (!isset($this->args[$this->argIndex])) {
                throw new InvalidArgumentException('Missing parameter for placeholder');
            }

            $nextArg = $this->args[$this->argIndex++];

            if ($nextArg instanceof AbstractNode) {
                if ($nextArg instanceof SkipNode and $this->stack !== 1) {
                    throw new InvalidArgumentException('Skip block in the main stack');
                }

                $node = $nextArg;
            } else {
                $node = match ($tokenEnum) {
                    TokenEnum::Int => new ValueNode($nextArg, ValueNodeTypeEnum::Int),
                    TokenEnum::Float => new ValueNode($nextArg, ValueNodeTypeEnum::Float),
                    TokenEnum::Array => self::processArray($nextArg),
                    TokenEnum::Id => is_array($nextArg) ? new ListNode(array_map(fn($value) => new IdentifierNode($value), $nextArg)) : new IdentifierNode($nextArg),
                    TokenEnum::Unknown => self::processUnknown($nextArg),
                    default => throw new InvalidArgumentException('Unexpected token value'),
                };
            }

            $astStack[$this->stack]->addNode($node);
        }

        if (count($this->args) !== $this->argIndex) {
            throw new InvalidArgumentException('Too many parameters');
        }

        if ($this->stack !== 0) {
            throw new InvalidArgumentException('Unexpected unclosed brace');
        }

        return $astStack[$this->stack];
    }

    private static function tokenize(string $query): array
    {
        $pattern = '/\{|\}|\?[dfan#]?|[^?{}]+/';
        preg_match_all($pattern, $query, $matches);

        return $matches[0];
    }

    private static function processUnknown(mixed $nextArg): AbstractNode
    {
        return match (true) {
            is_int($nextArg) => new ValueNode($nextArg, ValueNodeTypeEnum::Int),
            is_float($nextArg) => new ValueNode($nextArg, ValueNodeTypeEnum::Float),
            is_null($nextArg) => new ValueNode(null, ValueNodeTypeEnum::Null),
            is_bool($nextArg) => new ValueNode($nextArg, ValueNodeTypeEnum::Bool),
            is_string($nextArg) => new ValueNode($nextArg, ValueNodeTypeEnum::String),
            default => throw new InvalidArgumentException('Unexpected type in ?'),
        };
    }

    protected static function processArray(array $nextArg): AbstractNode
    {
        if (array_is_list($nextArg)) {
            return new ListNode(array_map(fn($value) => self::processUnknown($value), $nextArg));
        }

        return new ListNode(array_map(
            fn($key, $value) => new AssignmentNode(new IdentifierNode($key), self::processUnknown($value)),
            array_keys($nextArg),
            $nextArg
        ));
    }
}

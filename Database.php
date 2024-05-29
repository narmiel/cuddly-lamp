<?php

namespace FpDbTest;

use FpDbTest\Nodes\SkipNode;
use mysqli;

class Database implements DatabaseInterface
{
    private mysqli $mysqli;

    public function __construct(mysqli $mysqli)
    {
        $this->mysqli = $mysqli;
    }

    public function buildQuery(string $query, array $args = []): string
    {
        $parser = new Parser($args);

        return $parser->parse($query);
    }

    public function skip(): SkipNode
    {
        return new SkipNode();
    }
}

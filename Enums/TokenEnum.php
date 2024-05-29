<?php

namespace FpDbTest\Enums;

enum TokenEnum: string
{
    case Int = '?d';
    case Float = '?f';
    case Array = '?a';
    case Id = '?#';
    case Unknown = '?';
    case OpenBrace = '{';
    case CloseBrace = '}';
}

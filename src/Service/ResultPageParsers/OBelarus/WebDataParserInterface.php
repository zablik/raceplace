<?php

namespace App\Service\ResultPageParsers\OBelarus;

interface WebDataParserInterface
{
    public function parse(string $html): array;
}

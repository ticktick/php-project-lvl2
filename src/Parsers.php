<?php

namespace Differ\Parsers;

use Error;
use Symfony\Component\Yaml\Yaml;

function getParser(string $dataType): callable
{
    switch ($dataType) {
        case 'json':
            return fn($str) => json_decode($str);
        case 'yaml':
            return fn($str) => Yaml::parse($str, Yaml::PARSE_OBJECT_FOR_MAP);
        default:
            throw new Error("unknown data type: {$dataType}");
    }
}

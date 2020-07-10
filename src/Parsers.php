<?php

namespace Differ\Parsers;

use Symfony\Component\Yaml\Yaml;

function getParser(string $fileType): callable
{
    switch ($fileType) {
        case 'json':
            return fn($str) => json_decode($str, true);
        case 'yaml':
            return fn($str) => (array)Yaml::parse($str, Yaml::PARSE_OBJECT_FOR_MAP);
        default:
            return fn($str) => $str;
    }
}

<?php

namespace Differ\Differ;

use Funct\Collection;

use function Differ\Parsers\getParser;
use function Differ\Formatter\format;

const NEWLINE = PHP_EOL;

const TYPE_UNCHANGED = 'unchanged';
const TYPE_CHANGED = 'changed';
const TYPE_ADDED = 'added';
const TYPE_REMOVED = 'removed';
const TYPE_NESTED = 'nested';

/**
 * @param string $pathToFile1
 * @param string $pathToFile2
 * @return string
 * @throws \Exception
 */
function genDiff(string $pathToFile1, string $pathToFile2): string
{
    if (!fileExists($pathToFile1) || !fileExists($pathToFile1)) {
        throw new \Error("some of file paths are invalid");
    }
    try {
        $file1Fields = parseFields($pathToFile1);
        $file2Fields = parseFields($pathToFile2);
    } catch (\TypeError $e) {
        throw new \Error("some of files contain invalid data");
    }
    $tree = toDiffTree($file1Fields, $file2Fields);
    $formatted = format($tree);

    return $formatted;
}

function toNode(string $name, string $type, $oldValue = null, $newValue = null, array $children = [])
{
    $node = [
        'name' => $name,
        'type' => $type,
    ];
    $node['oldValue'] = $oldValue;
    $node['newValue'] = $newValue;
    if ($children) {
        $node['children'] = $children;
    }
    return $node;
}

function toDiffTree(object $structure1, object $structure2)
{
    $structure1Keys = getObjectKeys($structure1);
    $structure2Keys = getObjectKeys($structure2);
    $allKeys = Collection\union($structure1Keys, $structure2Keys);

    $tree = array_map(function ($key) use ($structure1, $structure2) {
        if (property_exists($structure1, $key) && !property_exists($structure2, $key)) {
            return toNode($key, TYPE_REMOVED, $structure1->$key);
        }
        if (!property_exists($structure1, $key) && property_exists($structure2, $key)) {
            return toNode($key, TYPE_ADDED, null, $structure2->$key);
        }
        if (is_object($structure1->$key) && is_object($structure2->$key)) {
            return toNode($key, TYPE_NESTED, null, null, toDiffTree($structure1->$key, $structure2->$key));
        }
        if ($structure1->$key === $structure2->$key) {
            return toNode($key, TYPE_UNCHANGED, $structure1->$key);
        }

        return toNode($key, TYPE_CHANGED, $structure1->$key, $structure2->$key);
    }, $allKeys);

    return $tree;
}

function getObjectKeys(object $obj)
{
    return array_keys(get_object_vars($obj));
}

function parseFields(string $pathToFile): object
{
    $fileContent = getFileContent($pathToFile);
    $parser = getParser(getType($pathToFile));
    return $parser($fileContent);
}

function getType(string $pathToFile): string
{
    $extension = pathinfo($pathToFile)['extension'];
    switch ($extension) {
        case 'json':
            return 'json';
        case 'yml':
        case 'yaml':
            return 'yaml';
        default:
            throw new \Error('unknown file type');
    }
}

function getFileContent(string $pathToFile): string
{
    return file_get_contents($pathToFile);
}

function fileExists($pathToFile)
{
    return file_exists($pathToFile);
}

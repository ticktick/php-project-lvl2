<?php

namespace Differ\Differ;

use Funct\Collection;
use Error;
use TypeError;

use function Differ\Parsers\getParser;
use function Differ\Formatter\format;

const TYPE_UNCHANGED = 'unchanged';
const TYPE_CHANGED = 'changed';
const TYPE_ADDED = 'added';
const TYPE_REMOVED = 'removed';
const TYPE_NESTED = 'nested';

/**
 * @param string $pathToFile1
 * @param string $pathToFile2
 * @param string $format
 * @return string
 */
function genDiff(string $pathToFile1, string $pathToFile2, string $format = 'pretty'): string
{
    if (!file_exists($pathToFile1) || !file_exists($pathToFile1)) {
        throw new Error("some of file paths are invalid");
    }
    try {
        $file1Fields = parseFields($pathToFile1);
        $file2Fields = parseFields($pathToFile2);
    } catch (TypeError $e) {
        throw new Error("some of files contain invalid data");
    }
    $tree = makeDiffTree($file1Fields, $file2Fields);
    return format($tree, $format);
}

function makeNode(string $name, string $type, $oldValue = null, $newValue = null, array $children = [])
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

function makeDiffTree(object $structure1, object $structure2)
{
    $structure1Keys = getObjectKeys($structure1);
    $structure2Keys = getObjectKeys($structure2);
    $allKeys = Collection\union($structure1Keys, $structure2Keys);

    $tree = array_map(function ($key) use ($structure1, $structure2) {
        if (property_exists($structure1, $key) && !property_exists($structure2, $key)) {
            return makeNode($key, TYPE_REMOVED, $structure1->$key);
        }
        if (!property_exists($structure1, $key) && property_exists($structure2, $key)) {
            return makeNode($key, TYPE_ADDED, null, $structure2->$key);
        }
        if (is_object($structure1->$key) && is_object($structure2->$key)) {
            return makeNode(
                $key,
                TYPE_NESTED,
                null,
                null,
                makeDiffTree($structure1->$key, $structure2->$key)
            );
        }
        if ($structure1->$key === $structure2->$key) {
            return makeNode($key, TYPE_UNCHANGED, $structure1->$key);
        }

        return makeNode($key, TYPE_CHANGED, $structure1->$key, $structure2->$key);
    }, $allKeys);

    return $tree;
}

function getObjectKeys(object $obj)
{
    return array_keys(get_object_vars($obj));
}

function parseFields(string $pathToFile): object
{
    $fileContent = file_get_contents($pathToFile);
    $fileExtension = getFileExtension($pathToFile);
    $fileType = getTypeByExtension($fileExtension);
    $parser = getParser($fileType);
    return $parser($fileContent);
}

function getFileExtension(string $pathToFile): string
{
    return pathinfo($pathToFile)['extension'];
}

function getTypeByExtension(string $fileExtension): string
{
    switch ($fileExtension) {
        case 'json':
            return 'json';
        case 'yml':
        case 'yaml':
            return 'yaml';
        default:
            throw new Error('unknown file type');
    }
}

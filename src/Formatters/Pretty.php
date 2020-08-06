<?php

namespace Differ\Formatters\Pretty;

use Error;

use const Differ\Differ\TYPE_ADDED;
use const Differ\Differ\TYPE_NESTED;
use const Differ\Differ\TYPE_REMOVED;
use const Differ\Differ\TYPE_UNCHANGED;
use const Differ\Differ\TYPE_CHANGED;

function format(array $tree)
{
    $resultOutput = array_map(function ($node) use ($tree) {
        return formatNode($node);
    }, $tree);

    return '{' . PHP_EOL . implode(PHP_EOL, $resultOutput) . PHP_EOL . '}';
}

function formatNode(array $node, int $depth = 1)
{
    $type = $node['type'];
    $key = $node['name'];
    $oldValue = $node['oldValue'];
    $newValue = $node['newValue'];

    switch ($type) {
        case TYPE_REMOVED:
            $indent = getShortIndent($depth);
            $formattedValue = formatValue($oldValue, $depth);
            return "{$indent}- {$key}: {$formattedValue}";
        case TYPE_ADDED:
            $indent = getShortIndent($depth);
            $formattedValue = formatValue($newValue, $depth);
            return "{$indent}+ {$key}: {$formattedValue}";
        case TYPE_UNCHANGED:
            $indent = getIndent($depth);
            $formattedValue = formatValue($oldValue, $depth);
            return "{$indent}{$key}: {$formattedValue}";
        case TYPE_CHANGED:
            $indent = getShortIndent($depth);
            $formattedNewValue = formatValue($newValue, $depth);
            $formattedOldValue = formatValue($oldValue, $depth);
            return "{$indent}+ {$key}: {$formattedNewValue}" . PHP_EOL . "{$indent}- {$key}: {$formattedOldValue}";
        case TYPE_NESTED:
            $indent = getIndent($depth);
            $depth += 1;
            $formattedValue = implode(PHP_EOL, array_map(function ($child) use ($depth) {
                return formatNode($child, $depth);
            }, $node['children']));
            return "{$indent}{$key}: {" . PHP_EOL . "{$formattedValue}" . PHP_EOL . "{$indent}}";
    }

    throw new Error('Unknown node type');
}

function formatValue($value, int $depth)
{
    if (is_object($value)) {
        $values = array_map(function ($key) use ($value, $depth) {
            return getIndent($depth + 1) . "{$key}: " . formatValue($value->$key, $depth + 1);
        }, array_keys(get_object_vars($value)));
        return '{' . PHP_EOL . implode(PHP_EOL, $values) . PHP_EOL . getIndent($depth) . '}';
    }
    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    }
    return $value;
}

function getShortIndent(int $depth)
{
    return getIndent($depth, true);
}

function getIndent(int $depth, bool $short = false)
{
    $spacesNeed = 4 * $depth;
    if ($short) {
        $spacesNeed -= 2;
    }
    return str_repeat(' ', $spacesNeed);
}

<?php

namespace Differ\Formatters\Pretty;

use Error;

use const Differ\Differ\TYPE_ADDED;
use const Differ\Differ\TYPE_NESTED;
use const Differ\Differ\TYPE_REMOVED;
use const Differ\Differ\TYPE_UNCHANGED;
use const Differ\Differ\TYPE_CHANGED;

function format(array $tree): string
{
    $lines = formatLines($tree);
    return "{\n" . implode("\n", $lines) . "\n}";
}

function formatLines(array $tree, int $depth = 1): array
{
    $formatNode = function ($node) use ($depth) {
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
                return "{$indent}+ {$key}: {$formattedNewValue}\n{$indent}- {$key}: {$formattedOldValue}";
            case TYPE_NESTED:
                $indent = getIndent($depth);
                $depth += 1;
                $formattedValue = implode("\n", formatLines($node['children'], $depth));
                return "{$indent}{$key}: {\n{$formattedValue}\n{$indent}}";
        }

        throw new Error("unknown node type: {$type}");
    };

    return array_map($formatNode, $tree);
}

function formatValue($value, int $depth)
{
    if (is_object($value)) {
        $values = array_map(function ($key) use ($value, $depth) {
            return getIndent($depth + 1) . "{$key}: " . formatValue($value->$key, $depth + 1);
        }, array_keys(get_object_vars($value)));
        return "{\n" . implode("\n", $values) . "\n" . getIndent($depth) . "}";
    }
    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    }
    return $value;
}

function getShortIndent(int $depth): string
{
    return getIndent($depth, true);
}

function getIndent(int $depth, bool $short = false): string
{
    $spacesNeed = 4 * $depth;
    if ($short) {
        $spacesNeed -= 2;
    }
    return str_repeat(' ', $spacesNeed);
}

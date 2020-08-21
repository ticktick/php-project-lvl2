<?php

namespace Differ\Formatters\Plain;

use Error;

use const Differ\Differ\TYPE_ADDED;
use const Differ\Differ\TYPE_NESTED;
use const Differ\Differ\TYPE_REMOVED;
use const Differ\Differ\TYPE_UNCHANGED;
use const Differ\Differ\TYPE_CHANGED;

function format(array $tree): string
{
    $lines = formatLines($tree);
    return implode("\n", $lines);
}

function formatLines(array $tree, string $keyPrefix = ''): array
{
    $formatNode = function ($node) use ($keyPrefix) {
        $type = $node['type'];
        $key = formatKey($keyPrefix, $node['name']);
        $oldValue = $node['oldValue'];
        $newValue = $node['newValue'];

        switch ($type) {
            case TYPE_REMOVED:
                return "Property '{$key}' was removed";
            case TYPE_ADDED:
                $formattedValue = formatValue($newValue);
                return "Property '{$key}' was added with value: '{$formattedValue}'";
            case TYPE_CHANGED:
                $formattedNewValue = formatValue($newValue);
                $formattedOldValue = formatValue($oldValue);
                return "Property '{$key}' was changed. From '{$formattedOldValue}' to '{$formattedNewValue}'";
            case TYPE_UNCHANGED:
                return null;
            case TYPE_NESTED:
                $formattedValues = formatLines($node['children'], $key);
                $formattedValues = array_filter($formattedValues);
                return implode("\n", $formattedValues);
        }

        throw new Error("unknown node type: {$type}");
    };

    return array_map($formatNode, $tree);
}

function formatKey($prefix, $key): string
{
    if (empty($prefix)) {
        return $key;
    }
    return "{$prefix}.{$key}";
}

function formatValue($value)
{
    if (is_object($value)) {
        return 'complex value';
    }
    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    }
    return $value;
}

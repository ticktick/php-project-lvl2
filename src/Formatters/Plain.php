<?php

namespace Differ\Formatters\Plain;

use Error;

use const Differ\Differ\NEWLINE;
use const Differ\Differ\TYPE_ADDED;
use const Differ\Differ\TYPE_NESTED;
use const Differ\Differ\TYPE_REMOVED;
use const Differ\Differ\TYPE_UNCHANGED;
use const Differ\Differ\TYPE_CHANGED;

function format(array $tree): string
{
    $resultOutput = array_map(function ($node) use ($tree) {
        return formatNode($node);
    }, $tree);

    return implode(NEWLINE, $resultOutput);
}

function formatNode(array $node, int $depth = 1, string $keyPrefix = '')
{
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
            return '';
        case TYPE_NESTED:
            $depth += 1;
            $formattedValues = array_map(function ($child) use ($depth, $key) {
                return formatNode($child, $depth, $key);
            }, $node['children']);
            $formattedValues = array_filter($formattedValues);
            return implode(NEWLINE, $formattedValues);
    }

    throw new Error('Unknown node type');
}

function formatKey($prefix, $key)
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
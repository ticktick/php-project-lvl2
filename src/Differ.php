<?php

namespace Differ;

use Funct\Collection;

const LINES_DELIMITER = "\n";

/**
 * @param string $pathToFile1
 * @param string $pathToFile2
 * @return string
 * @throws \Exception
 */
function genDiff(string $pathToFile1, string $pathToFile2): string
{
    if (!fileExists($pathToFile1) || !fileExists($pathToFile1)) {
        throw new \Exception("some of file paths are invalid");
    }
    try {
        $file1Fields = parseFields(getFileContents($pathToFile1));
        $file2Fields = parseFields(getFileContents($pathToFile2));
    } catch (\TypeError $e) {
        throw new \Exception("some of files contain invalid json");
    }

    $unchangedFields = getUnchangedFields($file1Fields, $file2Fields);
    $changedFields = getChangedFields($file1Fields, $file2Fields);
    $removedFields = getRemovedFields($file1Fields, $file2Fields);
    $addedFields = getAddedFields($file1Fields, $file2Fields);

    $unchangedFieldsOutput = formatUnchangedFields($unchangedFields, $file1Fields);
    $changedFieldsOutput = formatChangedFields($changedFields, $file1Fields, $file2Fields);
    $removedFieldsOutput = formatRemovedFields($removedFields, $file1Fields);
    $addedFieldsOutput = formatAddedFields($addedFields, $file2Fields);

    $resultOutput = join(LINES_DELIMITER, array_merge(
        $unchangedFieldsOutput,
        $changedFieldsOutput,
        $removedFieldsOutput,
        $addedFieldsOutput
    ));

    return '{' . LINES_DELIMITER . $resultOutput . LINES_DELIMITER . '}';
}

function getUnchangedFields(array $file1Fields, array $file2Fields): array
{
    return filterKeysByValuesCompare($file1Fields, $file2Fields, fn ($v1, $v2) => $v1 === $v2);
}

function getChangedFields(array $file1Fields, array $file2Fields): array
{
    return filterKeysByValuesCompare($file1Fields, $file2Fields, fn ($v1, $v2) => $v1 !== $v2);
}

function filterKeysByValuesCompare(array $baseFieldset, array $comparedFieldset, callable $compare)
{
    $baseFieldsetPairs = Collection\pairs($baseFieldset);
    $filteredFieldsPairs = array_filter($baseFieldsetPairs, function ($keyValuePair) use ($comparedFieldset, $compare) {
        [$key, $value] = $keyValuePair;
        if (!array_key_exists($key, $comparedFieldset)) {
            return false;
        }
        $comparedFieldsetValue = $comparedFieldset[$key];
        return $compare($value, $comparedFieldsetValue);
    });
    $filteredKeys = array_map(fn ($pair) => reset($pair), $filteredFieldsPairs);
    return $filteredKeys;
}

function getRemovedFields(array $file1Fields, array $file2Fields): array
{
    return getMissingFields($file1Fields, $file2Fields);
}

function getAddedFields(array $file1Fields, array $file2Fields): array
{
    return getMissingFields($file2Fields, $file1Fields);
}

function getMissingFields(array $baseFieldset, array $comparedFieldset)
{
    $addedFields = array_filter(array_keys($baseFieldset), function ($key) use ($comparedFieldset) {
        return !array_key_exists($key, $comparedFieldset);
    });
    return $addedFields;
}

function formatUnchangedFields(array $fields, array $file1Fields)
{
    return array_map(function ($field) use ($file1Fields) {
        return formatPair(' ', $field, $file1Fields[$field]);
    }, $fields);
}

function formatChangedFields(array $fields, array $file1Fields, array $file2Fields)
{
    return array_map(function ($field) use ($file1Fields, $file2Fields) {
        return formatPair('+', $field, $file2Fields[$field]) .
            LINES_DELIMITER .
            formatPair('-', $field, $file1Fields[$field]);
    }, $fields);
}

function formatRemovedFields(array $fields, array $file1Fields)
{
    return array_map(function ($field) use ($file1Fields) {
        return formatPair('-', $field, $file1Fields[$field]);
    }, $fields);
}

function formatAddedFields(array $fields, array $file2Fields)
{
    return array_map(function ($field) use ($file2Fields) {
        return formatPair('+', $field, $file2Fields[$field]);
    }, $fields);
}

function formatPair($prefix, $key, $value)
{
    if (!is_string($value) || !is_numeric($value)) {
        $value = json_encode($value);
    }
    return sprintf("    %s %s: %s", $prefix, $key, $value);
}

function parseFields(string $str): array
{
    return json_decode($str, true);
}

function getFileContents(string $pathToFile): string
{
    return file_get_contents($pathToFile);
}

function fileExists($pathToFile)
{
    return file_exists($pathToFile);
}

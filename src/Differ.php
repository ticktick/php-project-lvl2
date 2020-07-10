<?php

namespace Differ\Differ;

use Funct\Collection;

use function Differ\Parsers\getParser;

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
        throw new \Error("some of file paths are invalid");
    }
    try {
        $file1Fields = parseFields($pathToFile1);
        $file2Fields = parseFields($pathToFile2);
    } catch (\TypeError $e) {
        throw new \Error("some of files contain invalid data");
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

function parseFields(string $pathToFile): array
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

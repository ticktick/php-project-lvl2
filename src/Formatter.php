<?php

namespace Differ\Formatter;

use Error;

use function Differ\Formatters\Pretty\format as formatPretty;
use function Differ\Formatters\Json\format as formatJson;
use function Differ\Formatters\Plain\format as formatPlain;

function format(array $tree, string $type)
{
    switch ($type) {
        case 'pretty':
            return formatPretty($tree);
        case 'plain':
            return formatPlain($tree);
        case 'json':
            return formatJson($tree);
        default:
            throw new Error('Неизвестный формат');
    }
}

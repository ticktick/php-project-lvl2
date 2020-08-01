<?php

namespace Differ\Formatter;

use Error;

use function Differ\Formatters\Json\format as formatJson;
use function Differ\Formatters\Plain\format as formatPlain;

function format(array $tree, string $type)
{
    switch ($type) {
        case 'json':
            return formatJson($tree);
        case 'plain':
            return formatPlain($tree);
        default:
            throw new Error('Неизвестный формат');
    }
}

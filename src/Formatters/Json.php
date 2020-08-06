<?php

namespace Differ\Formatters\Json;

function format(array $tree)
{
    return json_encode($tree);
}

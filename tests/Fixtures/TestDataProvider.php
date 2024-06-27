<?php

declare(strict_types=1);

namespace Redmine\Tests\Fixtures;

use stdClass;

final class TestDataProvider
{
    public static function getInvalidProjectIdentifiers(): array
    {
        return [
            'null' => [null],
            'true' => [true],
            'false' => [false],
            'float' => [0.0],
            'array' => [[]],
            'object' => [new stdClass()],
        ];
    }
}

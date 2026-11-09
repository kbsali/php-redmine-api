<?php

declare(strict_types=1);

namespace Redmine\Api;

/**
 * Special values for the parameters of `create()` and `update()` methods.
 */
enum Param
{
    /**
     * Clears the field, e.g. `['description' => Param::Clear]`.
     *
     * Empty values like `''`, `[]`, `false` and `null` are not sent to Redmine.
     */
    case Clear;
}

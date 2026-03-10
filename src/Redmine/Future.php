<?php

declare(strict_types=1);

namespace Redmine;

/**
 * @internal
 */
final class Future
{
    private static bool $isForwardCompatibilityEnabled = false;

    public static function enableForwardCompatibility(): void
    {
        self::$isForwardCompatibilityEnabled = true;
    }

    public static function isForwardCompatibilityEnabled(): bool
    {
        return self::$isForwardCompatibilityEnabled;
    }
}

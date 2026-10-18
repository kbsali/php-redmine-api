<?php

declare(strict_types=1);

namespace Redmine\Tests\Fixtures;

use Closure;
use Redmine\Api\AbstractApi;

/**
 * Access the private future mode flag of an API instance in tests.
 */
final class FutureMode
{
    public static function enable(AbstractApi $api): void
    {
        Closure::bind(static function (AbstractApi $api): void {
            $api->futureMode = true;
        }, null, AbstractApi::class)($api);
    }

    public static function isEnabled(AbstractApi $api): bool
    {
        return Closure::bind(static function (AbstractApi $api): bool {
            return $api->futureMode;
        }, null, AbstractApi::class)($api);
    }
}

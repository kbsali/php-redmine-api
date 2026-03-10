<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Redmine\Future;

final class FutureTest extends TestCase
{
    public function testIsForwardCompatabilityEnabledReturnsFalseByDefault(): void
    {
        self::assertFalse(Future::isForwardCompatibilityEnabled());
    }
}

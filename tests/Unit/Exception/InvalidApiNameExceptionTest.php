<?php

namespace Redmine\Tests\Unit\Exception;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Exception as RedmineException;
use Redmine\Exception\InvalidApiNameException;

#[CoversClass(InvalidApiNameException::class)]
class InvalidApiNameExceptionTest extends TestCase
{
    public function testInvalidApiNameException(): void
    {
        $exception = new InvalidApiNameException();

        $this->assertInstanceOf(InvalidArgumentException::class, $exception);
        $this->assertInstanceOf(RedmineException::class, $exception);
    }
}

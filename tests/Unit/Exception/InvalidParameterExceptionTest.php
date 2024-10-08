<?php

namespace Redmine\Tests\Unit\Exception;

use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Redmine\Exception as RedmineException;
use Redmine\Exception\InvalidParameterException;

class InvalidParameterExceptionTest extends TestCase
{
    public function testInvalidParameterException(): void
    {
        $exception = new InvalidParameterException();

        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertInstanceOf(InvalidArgumentException::class, $exception);
        $this->assertInstanceOf(RedmineException::class, $exception);
    }
}

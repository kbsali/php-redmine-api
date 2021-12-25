<?php

namespace Redmine\Tests\Unit\Exception;

use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Redmine\Exception as RedmineException;
use Redmine\Exception\InvalidParameterException;

class InvalidParameterExceptionTest extends TestCase
{
    /**
     * @test
     */
    public function testInvalidParameterException()
    {
        $exception = new InvalidParameterException();

        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertInstanceOf(InvalidArgumentException::class, $exception);
        $this->assertInstanceOf(RedmineException::class, $exception);
    }
}

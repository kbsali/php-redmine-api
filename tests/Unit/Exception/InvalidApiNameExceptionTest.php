<?php

namespace Redmine\Tests\Unit\Exception;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Redmine\Exception as RedmineException;
use Redmine\Exception\InvalidApiNameException;

class InvalidApiNameExceptionTest extends TestCase
{
    /**
     * @test
     */
    public function testInvalidApiNameException()
    {
        $exception = new InvalidApiNameException();

        $this->assertInstanceOf(InvalidArgumentException::class, $exception);
        $this->assertInstanceOf(RedmineException::class, $exception);
    }
}

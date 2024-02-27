<?php

namespace Redmine\Tests\Unit\Exception;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Redmine\Exception as RedmineException;
use Redmine\Exception\InvalidApiNameException;

/**
 * @coversDefaultClass \Redmine\Exception\InvalidApiNameException
 */
class InvalidApiNameExceptionTest extends TestCase
{
    public function testInvalidApiNameException()
    {
        $exception = new InvalidApiNameException();

        $this->assertInstanceOf(InvalidArgumentException::class, $exception);
        $this->assertInstanceOf(RedmineException::class, $exception);
    }
}

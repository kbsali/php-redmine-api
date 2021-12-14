<?php

namespace Redmine\Tests\Unit\Exception;

use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Redmine\Exception as RedmineException;
use Redmine\Exception\MissingParameterException;

class MissingParameterExceptionTest extends TestCase
{
    /**
     * @test
     */
    public function testMissingParameterException()
    {
        $exception = new MissingParameterException();

        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertInstanceOf(InvalidArgumentException::class, $exception);
        $this->assertInstanceOf(RedmineException::class, $exception);
    }
}

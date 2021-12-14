<?php

namespace Redmine\Tests\Unit\Exception;

use Exception;
use PHPUnit\Framework\TestCase;
use Redmine\Exception as RedmineException;
use Redmine\Exception\ClientException;

class ClientExceptionTest extends TestCase
{
    /**
     * @test
     */
    public function testClientException()
    {
        $exception = new ClientException();

        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertInstanceOf(RedmineException::class, $exception);
    }
}

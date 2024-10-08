<?php

namespace Redmine\Tests\Unit\Exception;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Exception as RedmineException;
use Redmine\Exception\ClientException;

#[CoversClass(ClientException::class)]
class ClientExceptionTest extends TestCase
{
    public function testClientException(): void
    {
        $exception = new ClientException();

        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertInstanceOf(RedmineException::class, $exception);
    }
}

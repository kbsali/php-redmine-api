<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Exception;

use Exception;
use PHPUnit\Framework\TestCase;
use Redmine\Exception as RedmineException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Http\Response;

class UnexpectedResponseExceptionTest extends TestCase
{
    public function testCreateReturnsException(): void
    {
        $response = $this->createMock(Response::class);

        $exception = UnexpectedResponseException::create($response);

        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertInstanceOf(RedmineException::class, $exception);
    }

    public function testCreateWithThrowable(): void
    {
        $response = $this->createMock(Response::class);
        $throwable = new Exception('message', 5);

        $exception = UnexpectedResponseException::create($response, $throwable);

        $this->assertSame(5, $exception->getCode());
        $this->assertSame($throwable, $exception->getPrevious());
    }

    public function testGetResponseReturnsResponse(): void
    {
        $response = $this->createMock(Response::class);

        $exception = UnexpectedResponseException::create($response);

        $this->assertSame($response, $exception->getResponse());
        $this->assertInstanceOf(RedmineException::class, $exception);
    }
}

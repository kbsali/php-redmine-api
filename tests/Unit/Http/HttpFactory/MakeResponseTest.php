<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Http\HttpFactory;

use PHPUnit\Framework\TestCase;
use Redmine\Http\HttpFactory;
use Redmine\Http\Response;

/**
 * @covers \Redmine\Http\HttpFactory::makeResponse
 */
class MakeResponseTest extends TestCase
{
    public function testMakeResponse()
    {
        $response = HttpFactory::makeResponse(200, 'application/json', 'content');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/json', $response->getContentType());
        $this->assertSame('content', $response->getContent());
    }
}

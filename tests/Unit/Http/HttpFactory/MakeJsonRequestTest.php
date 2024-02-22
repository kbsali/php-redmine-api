<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Http\HttpFactory;

use PHPUnit\Framework\TestCase;
use Redmine\Http\HttpFactory;
use Redmine\Http\Request;

/**
 * @covers \Redmine\Http\HttpFactory::makeJsonRequest
 */
class MakeJsonRequestTest extends TestCase
{
    public function testMakeJsonRequest()
    {
        $response = HttpFactory::makeJsonRequest('GET', 'path.json', 'content');

        $this->assertInstanceOf(Request::class, $response);
        $this->assertSame('GET', $response->getMethod());
        $this->assertSame('path.json', $response->getPath());
        $this->assertSame('application/json', $response->getContentType());
        $this->assertSame('content', $response->getContent());
    }
}

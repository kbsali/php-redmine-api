<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Http\HttpFactory;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Http\HttpFactory;
use Redmine\Http\Request;

#[CoversClass(HttpFactory::class)]
class MakeRequestTest extends TestCase
{
    public function testMakeRequest()
    {
        $response = HttpFactory::makeRequest('GET', 'path.json', 'application/json', 'content');

        $this->assertInstanceOf(Request::class, $response);
        $this->assertSame('GET', $response->getMethod());
        $this->assertSame('path.json', $response->getPath());
        $this->assertSame('application/json', $response->getContentType());
        $this->assertSame('content', $response->getContent());
    }
}

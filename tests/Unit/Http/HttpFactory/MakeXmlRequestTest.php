<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Http\HttpFactory;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Http\HttpFactory;
use Redmine\Http\Request;

#[CoversClass(HttpFactory::class)]
class MakeXmlRequestTest extends TestCase
{
    public function testMakeXmlRequest()
    {
        $response = HttpFactory::makeXmlRequest('GET', 'path.xml', 'content');

        $this->assertInstanceOf(Request::class, $response);
        $this->assertSame('GET', $response->getMethod());
        $this->assertSame('path.xml', $response->getPath());
        $this->assertSame('application/xml', $response->getContentType());
        $this->assertSame('content', $response->getContent());
    }
}

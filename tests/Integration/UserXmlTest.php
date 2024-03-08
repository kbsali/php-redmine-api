<?php

namespace Redmine\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Redmine\Exception\MissingParameterException;
use Redmine\Tests\Fixtures\MockClient;

class UserXmlTest extends TestCase
{
    public function testCreateBlank()
    {
        /** @var \Redmine\Api\User */
        $api = MockClient::create()->getApi('user');
        $this->assertInstanceOf('Redmine\Api\User', $api);

        $this->expectException(MissingParameterException::class);
        $this->expectExceptionMessage('Theses parameters are mandatory: `login`, `lastname`, `firstname`, `mail`');

        $api->create();
    }
}

<?php

namespace Redmine\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Redmine\Exception\MissingParameterException;
use Redmine\Tests\Fixtures\MockClient;

class IssueCategoryXmlTest extends TestCase
{
    public function testCreateBlank(): void
    {
        /** @var \Redmine\Api\IssueCategory */
        $api = MockClient::create()->getApi('issue_category');
        $this->assertInstanceOf('Redmine\Api\IssueCategory', $api);

        $this->expectException(MissingParameterException::class);
        $this->expectExceptionMessage('Theses parameters are mandatory: `name`');

        $api->create('aProject');
    }
}

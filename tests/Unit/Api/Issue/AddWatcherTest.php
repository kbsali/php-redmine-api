<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Issue;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Issue;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Future;
use Redmine\Tests\Fixtures\AssertingHttpClient;
use SimpleXMLElement;

#[CoversClass(Issue::class)]
class AddWatcherTest extends TestCase
{
    /**
     * @dataProvider getAddWatcherData
     */
    #[DataProvider('getAddWatcherData')]
    public function testAddWatcherReturnsCorrectResponse(int $issueId, int $watcherUserId, string $expectedPath, string $expectedBody, int $responseCode, string $response): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'POST',
                $expectedPath,
                'application/xml',
                $expectedBody,
                $responseCode,
                'application/xml',
                $response,
            ],
        );

        // Create the object under test
        $api = Issue::fromHttpClient($client);

        // Perform the tests
        $return = $api->addWatcher($issueId, $watcherUserId);

        $this->assertInstanceOf(SimpleXMLElement::class, $return);
        $this->assertXmlStringEqualsXmlString($response, $return->asXml());
    }

    /**
     * @return array<array<mixed>>
     */
    public static function getAddWatcherData(): array
    {
        return [
            'test with integers' => [
                25,
                5,
                '/issues/25/watchers.xml',
                <<<XML
                <?xml version="1.0" encoding="UTF-8"?>
                <user_id>5</user_id>
                XML,
                201,
                '<?xml version="1.0" encoding="UTF-8"?><issue></issue>',
            ],
        ];
    }

    public function testAddWatcherReturnsEmptyString(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'POST',
                '/issues/1/watchers.xml',
                'application/xml',
                '<?xml version="1.0" encoding="UTF-8"?><user_id>2</user_id>',
                500,
                '',
                '',
            ],
        );

        // Create the object under test
        $api = Issue::fromHttpClient($client);

        // Perform the tests
        $return = $api->addWatcher(1, 2);

        $this->assertSame('', $return);
    }

    public function testAddWatcherWithIncorrectStatusCodeReturnsBody(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'POST',
                '/issues/1/watchers.xml',
                'application/xml',
                '<?xml version="1.0" encoding="UTF-8"?><user_id>2</user_id>',
                500,
                '',
                '<?xml version="1.0" encoding="UTF-8"?><error>error</error>',
            ],
        );

        $api = Issue::fromHttpClient($client);

        $return = $api->addWatcher(1, 2);

        $this->assertXmlStringEqualsXmlString('<?xml version="1.0" encoding="UTF-8"?><error>error</error>', $return->asXML());
    }

    public function testAddWatcherWithIncorrectStatusCodeThrowsException(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'POST',
                '/issues/1/watchers.xml',
                'application/xml',
                '<?xml version="1.0" encoding="UTF-8"?><user_id>2</user_id>',
                500,
                '',
                '',
            ],
        );

        $api = Issue::fromHttpClient($client);

        $this->expectException(UnexpectedResponseException::class);

        try {
            Future::enableForwardCompatibility();

            $api->addWatcher(1, 2);
        } finally {
            Future::disableForwardCompatibility();
        }
    }
}

<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Project;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Project;
use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Tests\Fixtures\AssertingHttpClient;
use Redmine\Tests\Fixtures\FutureMode;

#[CoversClass(Project::class)]
class ShowTest extends TestCase
{
    /**
     * @dataProvider getShowData
     *
     * @param mixed $identifier
     * @param array<mixed> $parameters
     * @param mixed $expectedReturn
     */
    #[DataProvider('getShowData')]
    public function testShowReturnsCorrectResponse($identifier, array $parameters, string $expectedPath, string $response, $expectedReturn): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                $expectedPath,
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = Project::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->show($identifier, $parameters));
    }

    /**
     * @return array<array<mixed>>
     */
    public static function getShowData(): array
    {
        return [
            'array response with integer id' => [
                5,
                [],
                '/projects/5.json?include=trackers%2Cissue_categories%2Cattachments%2Crelations',
                '["API Response"]',
                ['API Response'],
            ],
            'array response with string id' => [
                'test',
                [],
                '/projects/test.json?include=trackers%2Cissue_categories%2Cattachments%2Crelations',
                '["API Response"]',
                ['API Response'],
            ],
            'test include parameter as array' => [
                'test',
                [
                    'include' => ['resource1', 'resource2'],
                ],
                '/projects/test.json?include=resource1%2Cresource2',
                '["API Response"]',
                ['API Response'],
            ],
            'test include parameter as string will be ignored' => [
                'test',
                [
                    'include' => 'resource1,resource2',
                ],
                '/projects/test.json?include=trackers%2Cissue_categories%2Cattachments%2Crelations',
                '["API Response"]',
                ['API Response'],
            ],
            'array response with parameters' => [
                5,
                [
                    'parameter1',
                    'parameter2',
                    'enabled_modules',
                ],
                '/projects/5.json?0=parameter1&1=parameter2&2=enabled_modules&include=trackers%2Cissue_categories%2Cattachments%2Crelations',
                '["API Response"]',
                ['API Response'],
            ],
            'string response' => [
                5,
                [],
                '/projects/5.json?include=trackers%2Cissue_categories%2Cattachments%2Crelations',
                'string',
                (PHP_VERSION_ID < 80600) ? 'Error decoding body as JSON: Syntax error' : 'Error decoding body as JSON: Syntax error near location 1:1',
            ],
            'false response' => [
                5,
                [],
                '/projects/5.json?include=trackers%2Cissue_categories%2Cattachments%2Crelations',
                '',
                false,
            ],
        ];
    }

    public function testShowWithFutureModeReturnsCorrectResponse(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/projects/5.json?include=trackers%2Cissue_categories%2Cattachments%2Crelations',
                'application/json',
                '',
                200,
                'application/json',
                '["API Response"]',
            ],
        );

        $api = Project::fromHttpClient($client);

        FutureMode::enable($api);

        $this->assertSame(['API Response'], $api->show(5));
    }

    public function testShowWithIncorrectStatusCodeReturnsFalse(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/projects/5.json?include=trackers%2Cissue_categories%2Cattachments%2Crelations',
                'application/json',
                '',
                404,
                'application/json',
                '',
            ],
        );

        $api = Project::fromHttpClient($client);

        $this->assertFalse($api->show(5));
    }

    /**
     * @dataProvider getUnexpectedResponseData
     */
    #[DataProvider('getUnexpectedResponseData')]
    public function testShowWithUnexpectedResponseThrowsException(int $responseCode, string $responseContentType, string $response): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/projects/5.json?include=trackers%2Cissue_categories%2Cattachments%2Crelations',
                'application/json',
                '',
                $responseCode,
                $responseContentType,
                $response,
            ],
        );

        $api = Project::fromHttpClient($client);

        FutureMode::enable($api);

        try {
            $api->show(5);
            $this->fail('Expected UnexpectedResponseException was not thrown.');
        } catch (UnexpectedResponseException $e) {
            $this->assertSame($responseCode, $e->getResponse()->getStatusCode());
            $this->assertNull($e->getPrevious());
        }
    }

    /**
     * @return array<string, array<mixed>>
     */
    public static function getUnexpectedResponseData(): array
    {
        return [
            'status code 404 with empty body' => [404, 'application/json', ''],
            'status code 403 with empty body' => [403, 'application/json', ''],
            'status code 500 with HTML body' => [500, 'text/html', '<html><body>Internal Server Error</body></html>'],
            'status code 500 with JSON body' => [500, 'application/json', '["API Response"]'],
            'status code 200 with empty body' => [200, 'application/json', ''],
            'status code 200 with JSON null' => [200, 'application/json', 'null'],
            'status code 200 with JSON integer' => [200, 'application/json', '5'],
            'status code 200 with JSON string' => [200, 'application/json', '"text"'],
        ];
    }

    public function testShowWithInvalidJsonThrowsException(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/projects/5.json?include=trackers%2Cissue_categories%2Cattachments%2Crelations',
                'application/json',
                '',
                200,
                'application/json',
                'string',
            ],
        );

        $api = Project::fromHttpClient($client);

        FutureMode::enable($api);

        try {
            $api->show(5);
            $this->fail('Expected UnexpectedResponseException was not thrown.');
        } catch (UnexpectedResponseException $e) {
            $this->assertSame(200, $e->getResponse()->getStatusCode());
            $this->assertInstanceOf(SerializerException::class, $e->getPrevious());
        }
    }
}

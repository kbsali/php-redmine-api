<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\IssueCategory;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\IssueCategory;
use Redmine\Exception\MissingParameterException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Future;
use Redmine\Http\HttpClient;
use Redmine\Tests\Fixtures\AssertingHttpClient;
use SimpleXMLElement;

#[CoversClass(IssueCategory::class)]
class CreateTest extends TestCase
{
    /**
     * @dataProvider getCreateData
     *
     * @param string|int $identifier
     * @param array<mixed> $parameters
     */
    #[DataProvider('getCreateData')]
    public function testCreateReturnsCorrectResponse($identifier, array $parameters, string $expectedPath, string $expectedBody, int $responseCode, string $response): void
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
        $api = IssueCategory::fromHttpClient($client);

        // Perform the tests
        $return = $api->create($identifier, $parameters);

        $this->assertInstanceOf(SimpleXMLElement::class, $return);
        $this->assertXmlStringEqualsXmlString($response, $return->asXml());
    }

    /**
     * @return array<array<mixed>>
     */
    public static function getCreateData(): array
    {
        return [
            'test with minimal parameters' => [
                5,
                ['name' => 'Test Category'],
                '/projects/5/issue_categories.xml',
                '<?xml version="1.0" encoding="UTF-8"?><issue_category><name>Test Category</name></issue_category>',
                201,
                '<?xml version="1.0" encoding="UTF-8"?><issue_category></issue_category>',
            ],
            'test with minimal parameters and project identifier as string' => [
                'test-project',
                ['name' => 'Test Category'],
                '/projects/test-project/issue_categories.xml',
                '<?xml version="1.0" encoding="UTF-8"?><issue_category><name>Test Category</name></issue_category>',
                201,
                '<?xml version="1.0" encoding="UTF-8"?><issue_category></issue_category>',
            ],
            'test with all parameters' => [
                5,
                ['name' => 'Test Category', 'assigned_to_id' => 2],
                '/projects/5/issue_categories.xml',
                '<?xml version="1.0" encoding="UTF-8"?><issue_category><name>Test Category</name><assigned_to_id>2</assigned_to_id></issue_category>',
                201,
                '<?xml version="1.0" encoding="UTF-8"?><issue_category></issue_category>',
            ],
        ];
    }

    public function testCreateReturnsEmptyString(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'POST',
                '/projects/5/issue_categories.xml',
                'application/xml',
                '<?xml version="1.0" encoding="UTF-8"?><issue_category><name>Test Category</name></issue_category>',
                500,
                '',
                '',
            ],
        );

        // Create the object under test
        $api = IssueCategory::fromHttpClient($client);

        // Perform the tests
        $return = $api->create(5, ['name' => 'Test Category']);

        $this->assertSame('', $return);
    }

    public function testCreateWithIncorrectStatusCodeReturnsBody(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'POST',
                '/projects/5/issue_categories.xml',
                'application/xml',
                '<?xml version="1.0" encoding="UTF-8"?><issue_category><name>Test Category</name></issue_category>',
                500,
                '',
                '<error>error</error>',
            ],
        );

        $api = IssueCategory::fromHttpClient($client);

        $return = $api->create(5, ['name' => 'Test Category']);

        $this->assertInstanceOf(SimpleXMLElement::class, $return);
        $this->assertXmlStringEqualsXmlString('<error>error</error>', $return->asXml());
    }

    public function testCreateWithIncorrectStatusCodeThrowsException(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'POST',
                '/projects/5/issue_categories.xml',
                'application/xml',
                '<?xml version="1.0" encoding="UTF-8"?><issue_category><name>Test Category</name></issue_category>',
                500,
                '',
                '',
            ],
        );

        $api = IssueCategory::fromHttpClient($client);

        $this->expectException(UnexpectedResponseException::class);

        try {
            Future::enableForwardCompatibility();

            $api->create(5, ['name' => 'Test Category']);
        } finally {
            Future::disableForwardCompatibility();
        }
    }

    public function testCreateThrowsExceptionWithEmptyParameters(): void
    {
        // Create the used mock objects
        $client = $this->createStub(HttpClient::class);

        // Create the object under test
        $api = IssueCategory::fromHttpClient($client);

        $this->expectException(MissingParameterException::class);
        $this->expectExceptionMessage('Theses parameters are mandatory: `name');

        // Perform the tests
        $api->create(5);
    }

    /**
     * @dataProvider incompleteCreateParameterProvider
     *
     * @param array<mixed> $parameters
     */
    #[DataProvider('incompleteCreateParameterProvider')]
    public function testCreateThrowsExceptionIfMandatoyParametersAreMissing(array $parameters): void
    {
        // Create the used mock objects
        $client = $this->createStub(HttpClient::class);

        // Create the object under test
        $api = IssueCategory::fromHttpClient($client);

        $this->expectException(MissingParameterException::class);
        $this->expectExceptionMessage('Theses parameters are mandatory: `name');

        // Perform the tests
        $api->create('5', $parameters);
    }

    /**
     * Provider for incomplete create parameters.
     *
     * @return array<array<mixed>>
     */
    public static function incompleteCreateParameterProvider(): array
    {
        return [
            'missing all mandatory parameters' => [
                [],
            ],
            'missing `name` parameter' => [
                [
                    'assigned_to_id' => 2,
                ],
            ],
        ];
    }
}

<?php

namespace Redmine\Tests\Unit\Api\IssueCategory;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\IssueCategory;
use Redmine\Exception\MissingParameterException;
use Redmine\Http\HttpClient;
use Redmine\Tests\Fixtures\AssertingHttpClient;
use SimpleXMLElement;

#[CoversClass(IssueCategory::class)]
class CreateTest extends TestCase
{
    /**
     * @dataProvider getCreateData
     */
    #[DataProvider('getCreateData')]
    public function testCreateReturnsCorrectResponse($identifier, $parameters, $expectedPath, $expectedBody, $responseCode, $response)
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
                $response
            ]
        );

        // Create the object under test
        $api = new IssueCategory($client);

        // Perform the tests
        $return = $api->create($identifier, $parameters);

        $this->assertInstanceOf(SimpleXMLElement::class, $return);
        $this->assertXmlStringEqualsXmlString($response, $return->asXml());
    }

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

    public function testCreateReturnsEmptyString()
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
                ''
            ]
        );

        // Create the object under test
        $api = new IssueCategory($client);

        // Perform the tests
        $return = $api->create(5, ['name' => 'Test Category']);

        $this->assertSame('', $return);
    }

    public function testCreateThrowsExceptionWithEmptyParameters()
    {
        // Create the used mock objects
        $client = $this->createMock(HttpClient::class);

        // Create the object under test
        $api = new IssueCategory($client);

        $this->expectException(MissingParameterException::class);
        $this->expectExceptionMessage('Theses parameters are mandatory: `name');

        // Perform the tests
        $api->create(5);
    }

    /**
     * @dataProvider incompleteCreateParameterProvider
     */
    #[DataProvider('incompleteCreateParameterProvider')]
    public function testCreateThrowsExceptionIfMandatoyParametersAreMissing($parameters)
    {
        // Create the used mock objects
        $client = $this->createMock(HttpClient::class);

        // Create the object under test
        $api = new IssueCategory($client);

        $this->expectException(MissingParameterException::class);
        $this->expectExceptionMessage('Theses parameters are mandatory: `name');

        // Perform the tests
        $api->create('5', $parameters);
    }

    /**
     * Provider for incomplete create parameters.
     *
     * @return array[]
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

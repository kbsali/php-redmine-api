<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\TimeEntry;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\TimeEntry;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(TimeEntry::class)]
class UpdateTest extends TestCase
{
    /**
     * @dataProvider getUpdateData
     */
    #[DataProvider('getUpdateData')]
    public function testUpdateReturnsCorrectResponse($id, $parameters, $expectedPath, $expectedBody, $responseCode, $response): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'PUT',
                $expectedPath,
                'application/xml',
                $expectedBody,
                $responseCode,
                '',
                $response,
            ],
        );

        // Create the object under test
        $api = new TimeEntry($client);

        // Perform the tests
        $this->assertSame('', $api->update($id, $parameters));
    }

    public static function getUpdateData(): array
    {
        return [
            'test with comments' => [
                1,
                [
                    'hours' => '10.25',
                    'comments' => 'some text with xml entities: & < > " \' ',
                ],
                '/time_entries/1.xml',
                <<< XML
                <?xml version="1.0"?>
                <time_entry>
                    <id>1</id>
                    <hours>10.25</hours>
                    <comments>some text with xml entities: &amp; &lt; &gt; " ' </comments>
                </time_entry>
                XML,
                204,
                '',
            ],
            'test with custom fields' => [
                1,
                [
                    'custom_fields' => [
                        [
                            'id' => 1,
                            'name' => 'Affected version',
                            'value' => '1.0.1',
                        ],
                        [
                            'id' => 2,
                            'name' => 'Resolution',
                            'value' => 'Fixed',
                        ],
                    ],
                ],
                '/time_entries/1.xml',
                <<< XML
                <?xml version="1.0"?>
                <time_entry>
                    <id>1</id>
                    <custom_fields type="array">
                        <custom_field name="Affected version" id="1">
                            <value>1.0.1</value>
                        </custom_field>
                        <custom_field name="Resolution" id="2">
                            <value>Fixed</value>
                        </custom_field>
                    </custom_fields>
                </time_entry>
                XML,
                204,
                '',
            ],
        ];
    }
}

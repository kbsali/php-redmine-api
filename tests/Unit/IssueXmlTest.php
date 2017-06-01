<?php

namespace Redmine\Tests\Unit;

use Redmine\Tests\Fixtures\MockClient as TestClient;

class IssueXmlTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TestClient
     */
    private $client;

    public function setup()
    {
        $this->client = new TestClient('http://test.local', 'asdf');
    }

    public function testCreateBlank()
    {
        $api = $this->client->issue;
        $this->assertInstanceOf('Redmine\Api\Issue', $api);

        $xml = '<?xml version="1.0"?>
<issue/>';
        $res = $api->create();
        $this->assertEquals($this->formatXml($xml), $this->formatXml($res['data']));
    }

    public function testCreateComplexWithUpload()
    {
        $api = $this->client->issue;
        $res = $api->create([
            'project_id' => 'myproject',
            'subject' => 'A test issue',
            'description' => 'Here goes the issue description',
            'uploads' => [
                [
                  'token' => 'asdfasdfasdfasdf',
                  'filename' => 'MyFile.pdf',
                  'description' => 'MyFile is better then YourFile...',
                  'content_type' => 'application/pdf',
                ],
            ],
        ]);

        $xml = '<?xml version="1.0"?>
<issue>
    <subject>A test issue</subject>
    <description>Here goes the issue description</description>
    <project_id>myproject</project_id>
    <uploads type="array">
      <upload>
        <token>asdfasdfasdfasdf</token>
        <filename>MyFile.pdf</filename>
        <description>MyFile is better then YourFile...</description>
        <content_type>application/pdf</content_type>
      </upload>
    </uploads>
</issue>';
        $this->assertEquals($this->formatXml($xml), $this->formatXml($res['data']));
    }

    public function testCreateComplex()
    {
        $api = $this->client->issue;
        $res = $api->create([
            'project_id' => 'test',
            'subject' => 'test api (xml) 3',
            'description' => 'test api',
            'assigned_to_id' => 1,
            'custom_fields' => [
                [
                    'id' => 2,
                    'name' => 'Issuer',
                    'value' => 'asdf',
                ],
                [
                    'id' => 5,
                    'name' => 'Phone',
                    'value' => '9939494',
                ],
                [
                    'id' => '8',
                    'name' => 'Email',
                    'value' => 'asdf@asdf.com',
                ],
            ],
            'watcher_user_ids' => [],
        ]);

        $xml = '<?xml version="1.0"?>
<issue>
    <subject>test api (xml) 3</subject>
    <description>test api</description>
    <project_id>test</project_id>
    <assigned_to_id>1</assigned_to_id>
    <custom_fields type="array">
        <custom_field name="Issuer" id="2"><value>asdf</value></custom_field>
        <custom_field name="Phone" id="5"><value>9939494</value></custom_field>
        <custom_field name="Email" id="8"><value>asdf@asdf.com</value></custom_field>
    </custom_fields>
</issue>';
        $this->assertEquals($this->formatXml($xml), $this->formatXml($res['data']));
    }

    public function testCreateComplexWithLineBreakInDescription()
    {
        $api = $this->client->issue;
        $res = $api->create([
            'project_id' => 'test',
            'subject' => 'test api (xml) 3',
            'description' => "line1\nline2",
            'assigned_to_id' => 1,
            'custom_fields' => [
                [
                    'id' => 2,
                    'name' => 'Issuer',
                    'value' => 'asdf',
                ],
                [
                    'id' => 5,
                    'name' => 'Phone',
                    'value' => '9939494',
                ],
                [
                    'id' => '8',
                    'name' => 'Email',
                    'value' => 'asdf@asdf.com',
                ],
            ],
            'watcher_user_ids' => [],
        ]);

        $xml = '<?xml version="1.0"?>
<issue>
    <subject>test api (xml) 3</subject>
    <description>line1
line2</description>
    <project_id>test</project_id>
    <assigned_to_id>1</assigned_to_id>
    <custom_fields type="array">
        <custom_field name="Issuer" id="2"><value>asdf</value></custom_field>
        <custom_field name="Phone" id="5"><value>9939494</value></custom_field>
        <custom_field name="Email" id="8"><value>asdf@asdf.com</value></custom_field>
    </custom_fields>
</issue>';
        $this->assertEquals($this->formatXml($xml), $this->formatXml($res['data']));
    }

    public function testUpdateIssue()
    {
        $api = $this->client->issue;
        $res = $api->update(1, [
            'subject' => 'test note (xml) 1',
            'notes' => 'test note api',
            'assigned_to_id' => 1,
            'status_id' => 2,
            'priority_id' => 5,
            'due_date' => '2014-05-13',

            // not testable because this will trigger a status name to id resolving
            // 'status' => 'Resolved',
        ]);
        $xml = '<?xml version="1.0"?>
<issue>
    <id>1</id>
    <subject>test note (xml) 1</subject>
    <notes>test note api</notes>
    <priority_id>5</priority_id>
    <status_id>2</status_id>
    <assigned_to_id>1</assigned_to_id>
    <due_date>2014-05-13</due_date>
</issue>';
        $this->assertEquals($this->formatXml($xml), $this->formatXml($res['data']));
    }

    public function testAddNoteToIssue()
    {
        $api = $this->client->issue;
        $res = $api->addNoteToIssue(1, 'some comment');
        $xml = '<?xml version="1.0"?>
<issue>
    <id>1</id>
    <notes>some comment</notes>
</issue>';
        $this->assertEquals($this->formatXml($xml), $this->formatXml($res['data']));
    }

    private function formatXml($xml)
    {
        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML((new \SimpleXMLElement($xml))->asXML());

        return $dom->saveXML();
    }
}

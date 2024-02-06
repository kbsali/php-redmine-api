<?php

declare(strict_types=1);

namespace Redmine\Tests\End2End\Attachment;

use Redmine\Api\Attachment;
use Redmine\Api\Project;
use Redmine\Tests\End2End\ClientTestCase;
use Redmine\Tests\RedmineExtension\RedmineVersion;

class AttachmentTest extends ClientTestCase
{
    /**
     * @dataProvider provideRedmineVersions
     */
    public function testInteractions(RedmineVersion $redmineVersion): void
    {
        $client = $this->getNativeCurlClient($redmineVersion);

        // Create project
        /** @var Project */
        $projectApi = $client->getApi('project');

        $projectIdentifier = 'project-with-wiki';

        $xmlData = $projectApi->create(['name' => 'project with wiki', 'identifier' => $projectIdentifier]);

        $projectDataJson = json_encode($xmlData);
        $projectData = json_decode($projectDataJson, true);

        $this->assertIsArray($projectData, $projectDataJson);
        $this->assertSame($projectIdentifier, $projectData['identifier'], $projectDataJson);

        // Upload file
        /** @var Attachment */
        $attachmentApi = $client->getApi('attachment');

        $jsonData = $attachmentApi->upload(file_get_contents(dirname(__FILE__, 3) . '/Fixtures/testfile_01.txt'), ['filename' => 'testfile.txt']);

        $attachmentData = json_decode($jsonData, true);

        $this->assertIsArray($attachmentData, $jsonData);
        $this->assertArrayHasKey('upload', $attachmentData, $jsonData);
        $this->assertSame(
            ['id', 'token'],
            array_keys($attachmentData['upload']),
            $jsonData
        );

        $attachmentToken = $attachmentData['upload']['token'];

        $this->assertSame('1.7b962f8af22e26802b87abfa0b07b21dbd03b984ec8d6888dabd3f69cff162f8', $attachmentToken);

        // Check attachment
        $attachmentData = $attachmentApi->show($attachmentData['upload']['id']);

        $jsonData = json_encode($attachmentData);

        $this->assertIsArray($attachmentData, $jsonData);
        $this->assertArrayHasKey('attachment', $attachmentData, $jsonData);
        $this->assertSame(
            ['id', 'filename', 'filesize', 'content_type', 'description', 'content_url', 'author', 'created_on'],
            array_keys($attachmentData['attachment']),
            $jsonData
        );
    }
}

<?php

declare(strict_types=1);

namespace Redmine\Tests\End2End\Wiki;

use Redmine\Api\Attachment;
use Redmine\Api\Project;
use Redmine\Api\Wiki;
use Redmine\Tests\End2End\ClientTestCase;
use Redmine\Tests\RedmineExtension\RedmineVersion;

class WikiTest extends ClientTestCase
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

        // Add attachment to wiki page
        /** @var Wiki */
        $wikiApi = $client->getApi('wiki');

        $xmlData = $wikiApi->create($projectIdentifier, 'Test Page', [
            'text' => '# First Wiki page',
            'uploads' => [
                ['token' => $attachmentToken, 'filename' => 'filename.txt', 'content-type' => 'text/plain'],
            ],
        ]);

        $wikiDataJson = json_encode($xmlData);
        $wikiData = json_decode($wikiDataJson, true);

        $this->assertIsArray($wikiData, $wikiDataJson);
        $this->assertSame(
            ['title', 'text', 'version', 'author', 'comments', 'created_on', 'updated_on'],
            array_keys($wikiData),
            $wikiDataJson
        );
        $this->assertSame('Test+Page', $wikiData['title'], $wikiDataJson);

        // Check attachments
        $wikiData = $wikiApi->show($projectIdentifier, 'Test Page');

        $this->assertIsArray($wikiData, json_encode($wikiData));
        $this->assertIsArray($wikiData['wiki_page']['attachments'][0]);
        $this->assertSame(
            ['id', 'filename', 'filesize', 'content_type', 'description', 'content_url', 'author', 'created_on'],
            array_keys($wikiData['wiki_page']['attachments'][0])
        );

        // Update wiki page returns empty string
        $returnData = $wikiApi->update($projectIdentifier, 'Test Page', [
            'text' => '# First Wiki page with changes',
        ]);

        $this->assertSame('', $returnData, json_encode($returnData));

        // Remove wiki page
        $returnData = $wikiApi->remove($projectIdentifier, 'Test Page');

        $this->assertSame('', $returnData, json_encode($returnData));
    }
}

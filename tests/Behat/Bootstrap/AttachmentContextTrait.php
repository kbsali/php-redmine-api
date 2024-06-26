<?php

declare(strict_types=1);

namespace Redmine\Tests\Behat\Bootstrap;

use Behat\Gherkin\Node\TableNode;
use Redmine\Api\Attachment;

trait AttachmentContextTrait
{
    /**
     * @When I upload the content of the file :filepath with the following data
     */
    public function iUploadTheContentOfTheFileWithTheFollowingData(string $filepath, TableNode $table)
    {
        $data = [];

        foreach ($table as $row) {
            $data[$row['property']] = $row['value'];
        }

        $filepath = str_replace('%tests_dir%', dirname(__FILE__, 3), $filepath);

        /** @var Attachment */
        $api = $this->getNativeCurlClient()->getApi('attachment');

        $this->registerClientResponse(
            $api->upload(file_get_contents($filepath), $data),
            $api->getLastResponse(),
        );
    }

    /**
     * @When I update the attachment with the id :attachmentId with the following data
     */
    public function iUpdateTheAttachmentWithTheIdWithTheFollowingData(int $attachmentId, TableNode $table)
    {
        $data = [];

        foreach ($table as $row) {
            $data[$row['property']] = $row['value'];
        }

        /** @var Attachment */
        $api = $this->getNativeCurlClient()->getApi('attachment');

        $this->registerClientResponse(
            $api->update($attachmentId, $data),
            $api->getLastResponse(),
        );
    }

    /**
     * @When I show the attachment with the id :attachmentId
     */
    public function iShowTheAttachmentWithTheId(int $attachmentId)
    {
        /** @var Attachment */
        $api = $this->getNativeCurlClient()->getApi('attachment');

        $this->registerClientResponse(
            $api->show($attachmentId),
            $api->getLastResponse(),
        );
    }

    /**
     * @When I download the attachment with the id :attachmentId
     */
    public function iDownloadTheAttachmentWithTheId(int $attachmentId)
    {
        /** @var Attachment */
        $api = $this->getNativeCurlClient()->getApi('attachment');

        $this->registerClientResponse(
            $api->download($attachmentId),
            $api->getLastResponse(),
        );
    }

    /**
     * @When I remove the attachment with the id :attachmentId
     */
    public function iRemoveTheAttachmentWithTheId($attachmentId)
    {
        /** @var Attachment */
        $api = $this->getNativeCurlClient()->getApi('attachment');

        $this->registerClientResponse(
            $api->remove($attachmentId),
            $api->getLastResponse(),
        );
    }
}

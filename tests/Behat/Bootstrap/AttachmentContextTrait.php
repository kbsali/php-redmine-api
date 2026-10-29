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
    public function iUploadTheContentOfTheFileWithTheFollowingData(string $filepath, TableNode $table): void
    {
        $data = [];

        foreach ($table as $row) {
            $data[$row['property']] = $row['value'];
        }

        $filepath = str_replace('%tests_dir%', dirname(__FILE__, 3), $filepath);

        /** @var Attachment */
        $api = $this->getNativeCurlClient()->getApi('attachment');

        $this->registerClientCall(
            $api,
            fn() => $api->upload(file_get_contents($filepath), $data),
        );
    }

    /**
     * @When I update the attachment with the id :attachmentId with the following data
     */
    public function iUpdateTheAttachmentWithTheIdWithTheFollowingData(int $attachmentId, TableNode $table): void
    {
        $data = [];

        foreach ($table as $row) {
            $data[$row['property']] = $row['value'];
        }

        /** @var Attachment */
        $api = $this->getNativeCurlClient()->getApi('attachment');

        $this->registerClientCall(
            $api,
            fn() => $api->update($attachmentId, $data),
        );
    }

    /**
     * @When I show the attachment with the id :attachmentId
     */
    public function iShowTheAttachmentWithTheId(int $attachmentId): void
    {
        /** @var Attachment */
        $api = $this->getNativeCurlClient()->getApi('attachment');

        $this->registerClientCall(
            $api,
            fn() => $api->show($attachmentId),
        );
    }

    /**
     * @When I download the attachment with the id :attachmentId
     */
    public function iDownloadTheAttachmentWithTheId(int $attachmentId): void
    {
        /** @var Attachment */
        $api = $this->getNativeCurlClient()->getApi('attachment');

        $this->registerClientCall(
            $api,
            fn() => $api->download($attachmentId),
        );
    }

    /**
     * @When I remove the attachment with the id :attachmentId
     *
     * @param mixed $attachmentId
     */
    public function iRemoveTheAttachmentWithTheId($attachmentId): void
    {
        /** @var Attachment */
        $api = $this->getNativeCurlClient()->getApi('attachment');

        $this->registerClientCall(
            $api,
            fn() => $api->remove($attachmentId),
        );
    }
}

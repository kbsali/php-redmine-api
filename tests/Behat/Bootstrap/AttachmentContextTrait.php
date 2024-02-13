<?php

declare(strict_types=1);

namespace Redmine\Tests\Behat\Bootstrap;

use Behat\Behat\Tester\Exception\PendingException;
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
            $api->getLastResponse()
        );
    }
}

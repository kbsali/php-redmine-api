<?php

declare(strict_types=1);

namespace Redmine\Tests\Behat\Bootstrap;

use Redmine\Api\CustomField;

trait CustomFieldContextTrait
{
    /**
     * @When I list all custom fields
     */
    public function iListAllCustomFields()
    {
        /** @var CustomField */
        $api = $this->getNativeCurlClient()->getApi('custom_fields');

        $this->registerClientResponse(
            $api->list(),
            $api->getLastResponse(),
        );
    }
}

<?php

declare(strict_types=1);

namespace Redmine\Tests\Behat\Bootstrap;

use Redmine\Api\CustomField;

trait CustomFieldContextTrait
{
    /**
     * @Given I create a custom field for issues with the name :customFieldName
     */
    public function iCreateACustomFieldForIssuesWithTheName($customFieldName)
    {
        // support for creating custom fields via REST API is missing
        $this->redmine->excecuteDatabaseQuery(
            'INSERT INTO custom_fields(type, name, field_format, is_required, is_for_all, position) VALUES(:type, :name, :field_format, :is_required, :is_for_all, :position);',
            [],
            [
                ':type' => 'IssueCustomField',
                ':name' => $customFieldName,
                ':field_format' => 'string',
                ':is_required' => 0,
                ':is_for_all' => 1,
                ':position' => 1,
            ],
        );
    }

    /**
     * @Given I enable the tracker with ID :trackerId for custom field with ID :customFieldId
     */
    public function iEnableTheTrackerWithIdForCustomFieldWithId($trackerId, $customFieldId)
    {
        // support for enabling custom fields for trackers via REST API is missing
        $this->redmine->excecuteDatabaseQuery(
            'INSERT INTO custom_fields_trackers(custom_field_id, tracker_id) VALUES(:custom_field_id, :tracker_id);',
            [],
            [
                ':custom_field_id' => $customFieldId,
                ':tracker_id' => $trackerId,
            ],
        );
    }

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

    /**
     * @When I list all custom field names
     */
    public function iListAllCustomFieldNames()
    {
        /** @var CustomField */
        $api = $this->getNativeCurlClient()->getApi('custom_fields');

        $this->registerClientResponse(
            $api->listNames(),
            $api->getLastResponse(),
        );
    }
}

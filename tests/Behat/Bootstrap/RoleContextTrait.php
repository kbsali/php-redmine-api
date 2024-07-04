<?php

declare(strict_types=1);

namespace Redmine\Tests\Behat\Bootstrap;

use Redmine\Api\Role;

trait RoleContextTrait
{
    /**
     * @Given I have a role with the name :name
     */
    public function iHaveARoleWithTheName($name)
    {
        // support for creating issue status via REST API is missing
        $this->redmine->excecuteDatabaseQuery(
            'INSERT INTO roles(name, position, assignable, builtin, issues_visibility, users_visibility, time_entries_visibility, all_roles_managed, settings) VALUES(:name, :position, :assignable, :builtin, :issues_visibility, :users_visibility, :time_entries_visibility, :all_roles_managed, :settings);',
            [],
            [
                ':name' => $name,
                ':position' => 1,
                ':assignable' => 0,
                ':builtin' => 0,
                ':issues_visibility' => 'default',
                ':users_visibility' => 'all',
                ':time_entries_visibility' => 'all',
                ':all_roles_managed' => 1,
                ':settings' => '',
            ],
        );
    }

    /**
     * @When I list all roles
     */
    public function iListAllRoles()
    {
        /** @var Role */
        $api = $this->getNativeCurlClient()->getApi('role');

        $this->registerClientResponse(
            $api->list(),
            $api->getLastResponse(),
        );
    }

    /**
     * @When I list all role names
     */
    public function iListAllRoleNames()
    {
        /** @var Role */
        $api = $this->getNativeCurlClient()->getApi('role');

        $this->registerClientResponse(
            $api->listNames(),
            $api->getLastResponse(),
        );
    }
}

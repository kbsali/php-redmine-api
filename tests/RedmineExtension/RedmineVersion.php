<?php

declare(strict_types=1);

namespace Redmine\Tests\RedmineExtension;

enum RedmineVersion: int
{
    /**
     * Redmine 5.1.1
     *
     * @link https://www.redmine.org/versions/191
     * @link https://www.redmine.org/projects/redmine/wiki/Changelog_5_1#511-2023-11-27
     */
    case V5_1_1 = 50101;

    /**
     * Redmine 5.1.0
     *
     * @link https://www.redmine.org/versions/176
     * @link https://www.redmine.org/projects/redmine/wiki/Changelog_5_1#510-2023-10-31
     */
    case V5_1_0 = 50100;

    /**
     * Redmine 5.0.7
     *
     * @link https://www.redmine.org/versions/189
     * @link https://www.redmine.org/projects/redmine/wiki/Changelog_5_0#507-2023-11-27
     */
    case V5_0_7 = 50007;

    public function asString(): string
    {
        return match($this) {
            RedmineVersion::V5_0_7 => '5.0.7',
            RedmineVersion::V5_1_0 => '5.1.0',
            RedmineVersion::V5_1_1 => '5.1.1',
        };
    }

    public function asId(): int
    {
        return $this->value;
    }
}

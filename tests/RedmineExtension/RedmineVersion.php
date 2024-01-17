<?php

declare(strict_types=1);

namespace Redmine\Tests\RedmineExtension;

enum RedmineVersion: string
{
    /**
     * Redmine 5.1.1
     *
     * @link https://www.redmine.org/versions/191
     * @link https://www.redmine.org/projects/redmine/wiki/Changelog_5_1#511-2023-11-27
     */
    case V5_1_1 = '5.1.1';

    /**
     * Redmine 5.1.0
     *
     * @link https://www.redmine.org/versions/176
     * @link https://www.redmine.org/projects/redmine/wiki/Changelog_5_1#510-2023-10-31
     */
    case V5_1_0 = '5.1.0';

    /**
     * Redmine 5.0.7
     *
     * @link https://www.redmine.org/versions/189
     * @link https://www.redmine.org/projects/redmine/wiki/Changelog_5_0#507-2023-11-27
     */
    case V5_0_7 = '5.0.7';

    public function asString(): string
    {
        return $this->value;
    }

    /**
     * returns the version as integer ID, e.g. `50101`
     */
    public function asId(): int
    {
        $parts = explode('.', $this->value);

        return intval($parts[0]) * 10000 + intval($parts[1]) * 100 + intval($parts[2]);
    }
}

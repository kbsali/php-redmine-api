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

    /**
     * Redmine 5.0.6
     *
     * @link https://www.redmine.org/versions/188
     * @link https://www.redmine.org/projects/redmine/wiki/Changelog_5_0#506-2023-09-30
     */
    case V5_0_6 = '5.0.6';

    /**
     * Redmine 5.0.5
     *
     * @link https://www.redmine.org/versions/186
     * @link https://www.redmine.org/projects/redmine/wiki/Changelog_5_0#505-2023-03-05
     */
    case V5_0_5 = '5.0.5';

    /**
     * Redmine 5.0.4
     *
     * @link https://www.redmine.org/versions/184
     * @link https://www.redmine.org/projects/redmine/wiki/Changelog_5_0#504-2022-12-01
     */
    case V5_0_4 = '5.0.4';

    /**
     * Redmine 5.0.3
     *
     * @link https://www.redmine.org/versions/182
     * @link https://www.redmine.org/projects/redmine/wiki/Changelog_5_0#503-2022-10-02
     */
    case V5_0_3 = '5.0.3';

    /**
     * Redmine 5.0.2
     *
     * @link https://www.redmine.org/versions/180
     * @link https://www.redmine.org/projects/redmine/wiki/Changelog_5_0#502-2022-06-21
     */
    case V5_0_2 = '5.0.2';

    /**
     * Redmine 5.0.1
     *
     * @link https://www.redmine.org/versions/178
     * @link https://www.redmine.org/projects/redmine/wiki/Changelog_5_0#501-2022-05-16
     */
    case V5_0_1 = '5.0.1';

    /**
     * Redmine 5.0.0
     *
     * @link https://www.redmine.org/versions/155
     * @link https://www.redmine.org/projects/redmine/wiki/Changelog_5_0#500-2022-03-28
     */
    case V5_0_0 = '5.0.0';

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

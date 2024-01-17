<?php

declare(strict_types=1);

namespace Redmine\Tests\End2End;

use PHPUnit\Framework\TestCase;
use Redmine\Client\NativeCurlClient;
use Redmine\Tests\RedmineExtension\RedmineVersion;
use Redmine\Tests\RedmineExtension\TestRunnerTracer;

abstract class ClientTestCase extends TestCase
{
    /**
     * @return array<array<RedmineVersion>>
     */
    final public static function provideRedmineVersions(): array
    {
        $data = [];

        foreach (TestRunnerTracer::getSupportedRedmineVersions() as $version) {
            $data[] = [$version];
        }

        return $data;
    }

    protected function getNativeCurlClient(RedmineVersion $version): NativeCurlClient
    {
        $redmine = TestRunnerTracer::getRedmineInstance($version);

        return new NativeCurlClient(
            $redmine->getRedmineUrl(),
            $redmine->getApiKey()
        );
    }
}

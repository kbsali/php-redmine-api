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
     * @return RedmineVersion[]
     */
    final public static function getAvailableRedmineVersions(): array
    {
        return TestRunnerTracer::getSupportedRedmineVersions();
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

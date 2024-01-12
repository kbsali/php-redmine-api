<?php

declare(strict_types=1);

namespace Redmine\Tests\End2End;

use PHPUnit\Framework\TestCase;
use Redmine\Client\NativeCurlClient;
use Redmine\Tests\RedmineExtension\RedmineInstance;
use Redmine\Tests\RedmineExtension\TestRunnerTracer;

abstract class ClientTestCase extends TestCase
{
    /**
     * @return RedmineInstance[]
     */
    final public static function getAvailableRedmineInstances(): array
    {
        return TestRunnerTracer::getRedmineInstances();
    }

    protected function getNativeCurlClient(RedmineInstance $redmine): NativeCurlClient
    {
        return new NativeCurlClient(
            $redmine->getRedmineUrl(),
            $redmine->getApiKey()
        );
    }
}

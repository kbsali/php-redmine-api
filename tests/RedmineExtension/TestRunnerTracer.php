<?php

declare(strict_types=1);

namespace Redmine\Tests\RedmineExtension;

use PHPUnit\Event\Event;
use PHPUnit\Event\TestRunner\Finished;
use PHPUnit\Event\TestRunner\Started;
use PHPUnit\Event\Tracer\Tracer;

final class TestRunnerTracer implements Tracer
{
    /**
     * @var RedmineInstances[] $instances
     */
    private static $instances = [];

    /**
     * @return RedmineInstances[]
     */
    public static function getRedmineInstances(): array
    {
        return static::$instances;
    }

    /**
     * @return RedmineVersion[]
     */
    private static function getSupportedRedmineVersions(): array
    {
        return [
            RedmineVersion::V5_1_1,
            RedmineVersion::V5_0_7,
        ];
    }

    public function registerInstance(RedmineInstance $instance): void
    {
        static::$instances[$instance->getVersionId()] = $instance;
    }

    public function deregisterInstance(RedmineInstance $instance): void
    {
        unset(static::$instances[$instance->getVersionId()]);
    }

    public function trace(Event $event): void
    {
        if ($event instanceof Started) {
            foreach (static::getSupportedRedmineVersions() as $version) {
                RedmineInstance::create($this, $version);
            }
        }

        if ($event instanceof Finished) {
            foreach ($this->instances as $instance) {
                $instance->shutdown($this);
            }
        }
    }
}

<?php

declare(strict_types=1);

namespace Redmine\Tests\RedmineExtension;

use PHPUnit\Event\Event;
use PHPUnit\Event\Test\Finished as TestFinished;
use PHPUnit\Event\TestRunner\Finished as TestRunnerFinished;
use PHPUnit\Event\TestRunner\Started;
use PHPUnit\Event\Tracer\Tracer;
use RuntimeException;

final class TestRunnerTracer implements Tracer
{
    /**
     * @var RedmineInstance[] $instances
     */
    private static ?TestRunnerTracer $tracer = null;

    /**
     * @var RedmineInstance[] $instances
     */
    private static array $instances = [];

    /**
     * @return RedmineVersion[]
     */
    public static function getSupportedRedmineVersions(): array
    {
        return RedmineInstance::getSupportedVersions();
    }

    public static function getRedmineInstance(RedmineVersion $redmineVersion): RedmineInstance
    {
        if (static::$tracer === null) {
            throw new RuntimeException('You can only get a Redmine instance while the PHPUnit Test Runner is running.');
        }

        if (! array_key_exists($redmineVersion->asId(), static::$instances)) {
            RedmineInstance::create(static::$tracer, $redmineVersion);
        }

        return static::$instances[$redmineVersion->asId()];
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
            static::$tracer = $this;
        }

        if ($event instanceof TestFinished) {
            foreach (static::$instances as $instance) {
                $instance->reset($this);
            }
        }

        if ($event instanceof TestRunnerFinished) {
            foreach (static::$instances as $instance) {
                $instance->shutdown($this);
            }

            static::$tracer = null;
        }
    }
}

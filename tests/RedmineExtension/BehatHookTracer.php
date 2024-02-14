<?php

declare(strict_types=1);

namespace Redmine\Tests\RedmineExtension;

use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Testwork\Hook\Scope\AfterSuiteScope;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;
use Behat\Testwork\Hook\Scope\HookScope;
use RuntimeException;

final class BehatHookTracer implements InstanceRegistration
{
    /**
     * @var RedmineInstance[] $instances
     */
    private static ?BehatHookTracer $tracer = null;

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
            throw new RuntimeException('You can only get a Redmine instance while a Behat Suite is running.');
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

    public function hook(HookScope $event): void
    {
        if ($event instanceof BeforeSuiteScope) {
            static::$tracer = $this;
        }

        if ($event instanceof AfterScenarioScope) {
            foreach (static::$instances as $instance) {
                $instance->reset($this);
            }
        }

        if ($event instanceof AfterSuiteScope) {
            foreach (static::$instances as $instance) {
                $instance->shutdown($this);
            }

            static::$tracer = null;
        }
    }
}

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

    public static function getRedmineInstance(RedmineVersion $redmineVersion, string $rootPath): RedmineInstance
    {
        if (!self::$tracer instanceof \Redmine\Tests\RedmineExtension\BehatHookTracer) {
            throw new RuntimeException('You can only get a Redmine instance while a Behat Suite is running.');
        }

        if (! array_key_exists($redmineVersion->asId(), self::$instances)) {
            RedmineInstance::create(self::$tracer, $redmineVersion, $rootPath);
        }

        return self::$instances[$redmineVersion->asId()];
    }

    public function registerInstance(RedmineInstance $instance): void
    {
        self::$instances[$instance->getVersionId()] = $instance;
    }

    public function deregisterInstance(RedmineInstance $instance): void
    {
        unset(self::$instances[$instance->getVersionId()]);
    }

    public function hook(HookScope $event): void
    {
        if ($event instanceof BeforeSuiteScope) {
            self::$tracer = $this;
        }

        if ($event instanceof AfterScenarioScope) {
            foreach (self::$instances as $instance) {
                $instance->reset($this);
            }
        }

        if ($event instanceof AfterSuiteScope) {
            foreach (self::$instances as $instance) {
                $instance->shutdown($this);
            }

            self::$tracer = null;
        }
    }
}

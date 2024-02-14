<?php

declare(strict_types=1);

namespace Redmine\Tests\RedmineExtension;

interface InstanceRegistration
{
    public function registerInstance(RedmineInstance $instance): void;

    public function deregisterInstance(RedmineInstance $instance): void;
}

<?php

declare(strict_types=1);

namespace Redmine\Tests\RedmineExtension;

use PHPUnit\Event\Event;
use PHPUnit\Event\TestRunner\Finished;
use PHPUnit\Event\TestRunner\Started;
use PHPUnit\Event\Tracer\Tracer;

final class TestRunnerTracer implements Tracer
{
    public function trace(Event $event): void
    {
        if ($event instanceof Started) {
            // setup Redmine instances
        }

        if ($event instanceof Finished) {
            // teardown Redmine instances
        }
    }
}

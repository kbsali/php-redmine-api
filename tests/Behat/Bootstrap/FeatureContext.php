<?php

declare(strict_types=1);

namespace Redmine\Tests\Behat\Bootstrap;

use Behat\Behat\Context\Context;
use PHPUnit\Framework\TestCase;

final class FeatureContext extends TestCase implements Context
{
    /**
     * @Given an existing FeatureContext
     */
    public function anExistingFeaturecontext()
    {
        $this->assertTrue(true);
    }

    /**
     * @When I run the tests
     */
    public function iRunTheTests()
    {
        $this->assertTrue(true);
    }

    /**
     * @Then some testable outcome is achieved
     */
    public function someTestableOutcomeIsAchieved()
    {
        $this->assertTrue(true);
    }
}

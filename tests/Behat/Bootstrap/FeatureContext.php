<?php

declare(strict_types=1);

namespace Redmine\Tests\Behat\Bootstrap;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Testwork\Hook\Scope\AfterSuiteScope;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Project;
use Redmine\Client\NativeCurlClient;
use Redmine\Http\HttpClient;
use Redmine\Tests\RedmineExtension\BehatHookTracer;

final class FeatureContext extends TestCase implements Context
{
    private static ?BehatHookTracer $tracer = null;

    private static NativeCurlClient $client;

    /**
     * @BeforeSuite
     */
    public static function prepare(BeforeSuiteScope $scope)
    {
        static::$tracer = new BehatHookTracer();
        static::$tracer->hook($scope);

        $versions = static::$tracer::getSupportedRedmineVersions();

        $redmine = static::$tracer::getRedmineInstance(array_shift($versions));

        static::$client = new NativeCurlClient(
            $redmine->getRedmineUrl(),
            $redmine->getApiKey()
        );
    }

    /**
     * @AfterScenario
     */
    public static function reset(AfterScenarioScope $scope)
    {
        static::$tracer->hook($scope);
    }

    /**
     * @AfterSuite
     */
    public static function clean(AfterSuiteScope $scope)
    {
        static::$tracer->hook($scope);
        static::$tracer = null;
    }

    /**
     * @Given an existing FeatureContext
     */
    public function anExistingFeaturecontext()
    {
        // Create project
        /** @var Project */
        $projectApi = static::$client->getApi('project');

        $projectIdentifier = 'project-with-wiki';

        $xmlData = $projectApi->create(['name' => 'project with wiki', 'identifier' => $projectIdentifier]);

        $projectDataJson = json_encode($xmlData);
        $projectData = json_decode($projectDataJson, true);

        $this->assertIsArray($projectData, $projectDataJson);
        $this->assertSame($projectIdentifier, $projectData['identifier'], $projectDataJson);
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

<?php

declare(strict_types=1);

namespace Redmine\Tests\Behat\Bootstrap;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Testwork\Hook\Scope\AfterSuiteScope;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Project;
use Redmine\Client\Client;
use Redmine\Client\NativeCurlClient;
use Redmine\Http\Response;
use Redmine\Tests\RedmineExtension\BehatHookTracer;
use Redmine\Tests\RedmineExtension\RedmineInstance;

final class FeatureContext extends TestCase implements Context
{
    private static ?BehatHookTracer $tracer = null;

    /**
     * @BeforeSuite
     */
    public static function prepare(BeforeSuiteScope $scope)
    {
        static::$tracer = new BehatHookTracer();
        static::$tracer->hook($scope);
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

    private RedmineInstance $redmine;

    private Client $client;

    private Response $lastResponse;

    private mixed $lastReturn;

    /**
     * @Given I have a Redmine server with version :versionString
     */
    public function iHaveARedmineServerWithVersion(string $versionString)
    {
        $version = null;

        foreach (static::$tracer::getSupportedRedmineVersions() as $redmineVersion) {
            if ($redmineVersion->asString() === $versionString) {
                $version = $redmineVersion;
                break;
            }
        }

        if ($version === null) {
            throw new InvalidArgumentException('Redmine ' . $versionString . ' is not supported.');
        }

        $this->redmine = static::$tracer::getRedmineInstance($version);
    }

    /**
     * @Given I have a :clientName client
     */
    public function iHaveAClient($clientName)
    {
        if ($clientName !== 'NativeCurlClient') {
            throw new InvalidArgumentException('Client ' . $clientName . ' is not supported.');
        }

        $this->client = new NativeCurlClient(
            $this->redmine->getRedmineUrl(),
            $this->redmine->getApiKey()
        );
    }

    /**
     * @When I create a project with name :name and identifier :identifier
     */
    public function iCreateAProjectWithNameAndIdentifier($name, $identifier)
    {
        /** @var Project */
        $projectApi = $this->client->getApi('project');

        $this->lastReturn = $projectApi->create(['name' => $name, 'identifier' => $identifier]);
        $this->lastResponse = $projectApi->getLastResponse();
    }

    /**
     * @Then the response has the status code :statusCode
     */
    public function theResponseHasTheStatusCode(int $statusCode)
    {
        $this->assertSame($statusCode, $this->lastResponse->getStatusCode());
    }

    /**
     * @Then the response has the content type :contentType
     */
    public function theResponseHasTheContentType(string $contentType)
    {
        $this->assertStringStartsWith($contentType, $this->lastResponse->getContentType());
    }
}

<?php

declare(strict_types=1);

namespace Redmine\Tests\Behat\Bootstrap;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
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
use Redmine\Tests\RedmineExtension\RedmineVersion;
use SimpleXMLElement;

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

    public function __construct(string $redmineVersion)
    {
        $version = RedmineVersion::tryFrom($redmineVersion);

        if ($version === null) {
            throw new InvalidArgumentException('Redmine ' . $redmineVersion . ' is not supported.');
        }

        $this->redmine = static::$tracer::getRedmineInstance($version);

        parent::__construct('BehatRedmine' . $version->asId());
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
    public function iCreateAProjectWithNameAndIdentifier(string $name, string $identifier)
    {
        /** @var Project */
        $projectApi = $this->client->getApi('project');

        $this->lastReturn = $projectApi->create(['name' => $name, 'identifier' => $identifier]);
        $this->lastResponse = $projectApi->getLastResponse();
    }

    /**
     * @When I create a project with name :name, identifier :identifier and the following data
     */
    public function iCreateAProjectWithNameIdentifierAndTheFollowingData(string $name, string $identifier, TableNode $table)
    {
        $data = [];

        foreach ($table as $row) {
            $data[$row['key']] = $row['value'];
        }

        $data['name'] = $name;
        $data['identifier'] = $identifier;

        /** @var Project */
        $projectApi = $this->client->getApi('project');

        $this->lastReturn = $projectApi->create($data);
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

    /**
     * @Then the returned data is an instance of :className
     */
    public function theReturnedDataIsAnInstanceOf(string $className)
    {
        $this->assertInstanceOf($className, $this->lastReturn);
    }

    /**
     * @Then the returned data has the following properties
     */
    public function theReturnedDataHasTheFollowingProperties(PyStringNode $string)
    {
        $properties = [];

        if ($this->lastReturn instanceof SimpleXMLElement) {
            $properties = array_keys(get_object_vars($this->lastReturn));

            $this->assertSame($string->getStrings(), $properties);
        } else {
            throw new PendingException();
        }
    }
}

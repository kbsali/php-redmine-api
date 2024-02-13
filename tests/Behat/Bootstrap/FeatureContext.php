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
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Project;
use Redmine\Client\Client;
use Redmine\Client\NativeCurlClient;
use Redmine\Http\Response;
use Redmine\Tests\RedmineExtension\BehatHookTracer;
use Redmine\Tests\RedmineExtension\RedmineInstance;
use Redmine\Tests\RedmineExtension\RedmineVersion;
use RuntimeException;
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

    private array $lastReturnAsArray;

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
        $table = new TableNode([
            ['property', 'value'],
            ['name', $name],
            ['identifier', $identifier],
        ]);

        $this->iCreateAProjectWithTheFollowingData($table);
    }

    /**
     * @When I create a project with the following data
     */
    public function iCreateAProjectWithTheFollowingData(TableNode $table)
    {
        $data = [];

        foreach ($table as $row) {
            $data[$row['property']] = $row['value'];
        }

        /** @var Project */
        $projectApi = $this->client->getApi('project');

        unset($this->lastReturnAsArray);
        $this->lastReturn = $projectApi->create($data);
        $this->lastResponse = $projectApi->getLastResponse();
    }

    /**
     * @When I list all projects
     */
    public function iListAllProjects()
    {
        /** @var Project */
        $projectApi = $this->client->getApi('project');

        unset($this->lastReturnAsArray);
        $this->lastReturn = $projectApi->list();
        $this->lastResponse = $projectApi->getLastResponse();
    }

    /**
     * @Then the response has the status code :statusCode
     */
    public function theResponseHasTheStatusCode(int $statusCode)
    {
        $this->assertSame(
            $statusCode,
            $this->lastResponse->getStatusCode(),
            'Raw response content: ' . $this->lastResponse->getContent()
        );
    }

    /**
     * @Then the response has the content type :contentType
     */
    public function theResponseHasTheContentType(string $contentType)
    {
        $this->assertStringStartsWith(
            $contentType,
            $this->lastResponse->getContentType(),
            'Raw response content: ' . $this->lastResponse->getContent()
        );
    }

    /**
     * @Then the returned data is an instance of :className
     */
    public function theReturnedDataIsAnInstanceOf(string $className)
    {
        $this->assertInstanceOf($className, $this->lastReturn);
    }

    /**
     * @Then the returned data has only the following properties
     */
    public function theReturnedDataHasOnlyTheFollowingProperties(PyStringNode $string)
    {
        $properties = array_keys($this->getLastReturnAsArray());

        $this->assertSame($string->getStrings(), $properties);
    }

    /**
     * @Then the returned data has proterties with the following data
     */
    public function theReturnedDataHasProtertiesWithTheFollowingData(TableNode $table)
    {
        $returnData = $this->getLastReturnAsArray();

        if (! is_array($returnData)) {
            throw new Exception('Last return could not converted to array.');
        }

        foreach ($table as $row) {
            $this->assertArrayHasKey($row['property'], $returnData);

            $value = $returnData[$row['property']];

            if ($value instanceof SimpleXMLElement) {
                $value = strval($value);
            }

            $expected = $row['value'];

            // Handle expected int values
            if (is_int($value) && ctype_digit($expected)) {
                $expected = intval($expected);
            }

            $this->assertSame($expected, $value, 'Error with property ' . $row['property']);
        }
    }

    /**
     * @Then the returned data :property property is an array
     */
    public function theReturnedDataPropertyIsAnArray($property)
    {
        $returnData = $this->getLastReturnAsArray();

        $value = $returnData[$property] ?? null;

        $this->assertIsArray($value);
    }

    /**
     * @Then the returned data :property property containts :count items
     */
    public function theReturnedDataPropertyContaintsItems($property, int $count)
    {
        $returnData = $this->getLastReturnAsArray();

        $value = $returnData[$property] ?? null;

        $this->assertCount($count, $value);
    }

    private function getLastReturnAsArray(): array
    {
        if (isset($this->lastReturnAsArray)) {
            return $this->lastReturnAsArray;
        }

        if ($this->lastReturn instanceof SimpleXMLElement) {
            $returnData = json_decode(json_encode($this->lastReturn), true);
        } else if (is_array($this->lastReturn)) {
            $returnData = $this->lastReturn;
        }

        if (! is_array($returnData)) {
            throw new RuntimeException(sprintf(
                'the last returned data "%s" could not parsed into an array.',
                json_encode($this->lastReturn),
            ));
        }

        $this->lastReturnAsArray = $returnData;

        return $this->lastReturnAsArray;
    }
}

<?php

declare(strict_types=1);

namespace Redmine\Tests\Behat\Bootstrap;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Testwork\Hook\Scope\AfterSuiteScope;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Redmine\Client\NativeCurlClient;
use Redmine\Http\Response;
use Redmine\Tests\RedmineExtension\BehatHookTracer;
use Redmine\Tests\RedmineExtension\RedmineInstance;
use Redmine\Tests\RedmineExtension\RedmineVersion;
use RuntimeException;
use SimpleXMLElement;

final class FeatureContext extends TestCase implements Context
{
    use AttachmentContextTrait;
    use GroupContextTrait;
    use IssueCategoryContextTrait;
    use IssueContextTrait;
    use IssuePriorityContextTrait;
    use IssueRelationContextTrait;
    use IssueStatusContextTrait;
    use ProjectContextTrait;
    use TimeEntryActivityContextTrait;
    use TimeEntryContextTrait;
    use TrackerContextTrait;
    use UserContextTrait;
    use VersionContextTrait;
    use WikiContextTrait;

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

    private NativeCurlClient $client;

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

    private function getNativeCurlClient(): NativeCurlClient
    {
        return $this->client;
    }

    private function registerClientResponse(mixed $lastReturn, Response $lastResponse): void
    {
        unset($this->lastReturnAsArray);
        $this->lastReturn = $lastReturn;
        $this->lastResponse = $lastResponse;
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
     * @Then the response has an empty content type
     */
    public function theResponseHasAnEmptyContentType()
    {
        $this->assertSame('', $this->lastResponse->getContentType());
    }

    /**
     * @Then the response has the content :content
     */
    public function theResponseHasTheContent(string $content)
    {
        $this->assertSame($content, $this->lastResponse->getContent());
    }

    /**
     * @Then the response has the content
     */
    public function theResponseHasTheContentWithMultipleLines(PyStringNode $string)
    {
        $this->assertSame($string->getRaw(), $this->lastResponse->getContent());
    }

    /**
     * @Then the returned data is true
     */
    public function theReturnedDataIsTrue()
    {
        $this->assertTrue($this->lastReturn);
    }

    /**
     * @Then the returned data is false
     */
    public function theReturnedDataIsFalse()
    {
        $this->assertFalse($this->lastReturn);
    }

    /**
     * @Then the returned data is exactly :content
     */
    public function theReturnedDataIsExactly(string $content)
    {
        $this->assertSame($content, $this->lastReturn);
    }

    /**
     * @Then the returned data is exactly
     */
    public function theReturnedDataIsExactlyWithMultipleLines(PyStringNode $string)
    {
        $this->assertSame($string->getRaw(), $this->lastReturn);
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
        $this->theReturnedDataPropertyHasOnlyTheFollowingProperties(null, $string);
    }

    /**
     * @Then the returned data has proterties with the following data
     */
    public function theReturnedDataHasProtertiesWithTheFollowingData(TableNode $table)
    {
        $this->theReturnedDataPropertyContainsTheFollowingData(null, $table);
    }

    /**
     * @Then the returned data :property property is an array
     */
    public function theReturnedDataPropertyIsAnArray($property)
    {
        $returnData = $this->getLastReturnAsArray();

        $value = $this->getItemFromArray($returnData, $property);

        $this->assertIsArray($value);
    }

    /**
     * @Then the returned data :property property contains :count items
     */
    public function theReturnedDataPropertyContainsItems($property, int $count)
    {
        $returnData = $this->getLastReturnAsArray();

        $value = $this->getItemFromArray($returnData, $property);

        $this->assertIsArray($value);
        $this->assertCount($count, $value);
    }

    /**
     * @Then the returned data :property property contains the following data
     */
    public function theReturnedDataPropertyContainsTheFollowingData($property, TableNode $table)
    {
        $returnData = $this->getItemFromArray($this->getLastReturnAsArray(), $property);

        if (! is_array($returnData)) {
            throw new RuntimeException('The returned data on property "' . $property . '" is not an array.');
        }

        foreach ($table as $row) {
            $this->assertArrayHasKey($row['property'], $returnData);

            $value = $returnData[$row['property']];

            if ($value instanceof SimpleXMLElement) {
                $value = strval($value);
            }

            $expected = $row['value'];

            // Handle expected empty array
            if ($value === [] && $expected === '[]') {
                $expected = [];
            }

            // Handle expected int values
            if (is_int($value) && ctype_digit($expected)) {
                $expected = intval($expected);
            }

            // Handle expected float values
            if (is_float($value) && is_numeric($expected)) {
                $expected = floatval($expected);
            }

            // Handle expected null value
            if ($value === null && $expected === 'null') {
                $expected = null;
            }

            // Handle expected true value
            if ($value === true && $expected === 'true') {
                $expected = true;
            }

            // Handle expected false value
            if ($value === false && $expected === 'false') {
                $expected = false;
            }

            // Handle placeholder %redmine_id%
            if (is_string($expected)) {
                $expected = str_replace('%redmine_id%', strval($this->redmine->getVersionId()), $expected);
            }

            $this->assertSame($expected, $value, 'Error with property "' . $row['property'] . '"');
        }
    }

    /**
     * @Then the returned data :property property has only the following properties
     */
    public function theReturnedDataPropertyHasOnlyTheFollowingProperties($property, PyStringNode $string)
    {
        $value = $this->getItemFromArray($this->getLastReturnAsArray(), $property);

        $properties = array_keys($value);

        $this->assertSame($string->getStrings(), $properties);
    }

    /**
     * @Then the returned data :property property has only the following properties with Redmine version :versionComparision
     */
    public function theReturnedDataPropertyHasOnlyTheFollowingPropertiesWithRedmineVersion($property, string $versionComparision, PyStringNode $string)
    {
        $parts = explode(' ', $versionComparision);

        $redmineVersion = RedmineVersion::tryFrom($parts[1]);

        if ($redmineVersion === null) {
            throw new InvalidArgumentException('Comparison with Redmine ' . $versionComparision . ' is not supported.');
        }

        if (version_compare($this->redmine->getVersionString(), $parts[1], $parts[0])) {
            $this->theReturnedDataPropertyHasOnlyTheFollowingProperties($property, $string);
        }
    }

    private function getLastReturnAsArray(): array
    {
        if (isset($this->lastReturnAsArray)) {
            return $this->lastReturnAsArray;
        }

        $returnData = null;

        if ($this->lastReturn instanceof SimpleXMLElement) {
            $returnData = json_decode(json_encode($this->lastReturn), true);
        } elseif (is_string($this->lastReturn)) {
            $returnData = json_decode($this->lastReturn, true);
        } elseif (is_array($this->lastReturn)) {
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

    /**
     * Get item from an array by key supporting "dot" notation.
     */
    private function getItemFromArray(array $array, $key): mixed
    {
        if ($key === null) {
            return $array;
        }

        foreach (explode('.', $key) as $segment) {
            if (! array_key_exists($segment, $array)) {
                return null;
            }

            $array = $array[$segment];
        }

        return $array;
    }
}

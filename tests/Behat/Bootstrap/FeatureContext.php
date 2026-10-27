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
use Redmine\Api\AbstractApi;
use Redmine\Client\NativeCurlClient;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Http\Response;
use Redmine\Tests\RedmineExtension\BehatHookTracer;
use Redmine\Tests\RedmineExtension\RedmineInstance;
use Redmine\Tests\RedmineExtension\RedmineVersion;
use RuntimeException;
use SimpleXMLElement;

final class FeatureContext implements Context
{
    use AttachmentContextTrait;
    use CustomFieldContextTrait;
    use GroupContextTrait;
    use IssueCategoryContextTrait;
    use IssueContextTrait;
    use IssuePriorityContextTrait;
    use IssueRelationContextTrait;
    use IssueStatusContextTrait;
    use MembershipContextTrait;
    use ProjectContextTrait;
    use RoleContextTrait;
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
    public static function prepare(BeforeSuiteScope $scope): void
    {
        self::$tracer = new BehatHookTracer();
        self::$tracer->hook($scope);
    }

    /**
     * @AfterScenario
     */
    public static function reset(AfterScenarioScope $scope): void
    {
        self::$tracer->hook($scope);
    }

    /**
     * @AfterScenario
     */
    public function assertNoUnassertedException(): void
    {
        if ($this->lastException instanceof UnexpectedResponseException && ! $this->lastExceptionWasAsserted) {
            throw new RuntimeException(
                'The API call threw ' . get_class($this->lastException) . ', but no step asserted it.',
                0,
                $this->lastException,
            );
        }
    }

    /**
     * @AfterSuite
     */
    public static function clean(AfterSuiteScope $scope): void
    {
        self::$tracer->hook($scope);
        self::$tracer = null;
    }

    private RedmineInstance $redmine;

    private NativeCurlClient $client;

    private Response $lastResponse;

    /**
     * @var mixed
     */
    private $lastReturn;

    /**
     * @var array<mixed>
     */
    private array $lastReturnAsArray;

    private ?UnexpectedResponseException $lastException = null;

    private bool $lastExceptionWasAsserted = false;

    public function __construct(string $redmineVersion, string $rootPath, string $redmineUrl)
    {
        $version = RedmineVersion::tryFrom($redmineVersion);

        if (!$version instanceof RedmineVersion) {
            throw new InvalidArgumentException('Redmine ' . $redmineVersion . ' is not supported.');
        }

        $this->redmine = self::$tracer::getRedmineInstance($version, $rootPath, $redmineUrl);
    }

    /**
     * @Given I have a :clientName client
     *
     * @param mixed $clientName
     */
    public function iHaveAClient($clientName): void
    {
        $this->client = $this->createClient($clientName);
        $this->client->enableFutureMode();
    }

    /**
     * @todo remove this step and all scenarios tagged with `@without-future-mode` in v3.0.0
     *
     * @Given I have a :clientName client without future mode
     *
     * @param mixed $clientName
     */
    public function iHaveAClientWithoutFutureMode($clientName): void
    {
        $this->client = $this->createClient($clientName);
    }

    /**
     * @param mixed $clientName
     */
    private function createClient($clientName): NativeCurlClient
    {
        if ($clientName !== 'NativeCurlClient') {
            throw new InvalidArgumentException('Client ' . $clientName . ' is not supported.');
        }

        return new NativeCurlClient(
            $this->redmine->getRedmineUrl(),
            $this->redmine->getApiKey(),
        );
    }

    private function getNativeCurlClient(): NativeCurlClient
    {
        return $this->client;
    }

    /**
     * @param mixed $lastReturn
     */
    private function registerClientResponse($lastReturn, Response $lastResponse): void
    {
        unset($this->lastReturnAsArray);
        $this->lastReturn = $lastReturn;
        $this->lastResponse = $lastResponse;
        $this->lastException = null;
        $this->lastExceptionWasAsserted = false;
    }

    /**
     * Calls the API and registers the response, even if an UnexpectedResponseException is thrown.
     *
     * @param callable(): mixed $apiCall
     */
    private function registerClientCall(AbstractApi $api, callable $apiCall): void
    {
        try {
            $lastReturn = $apiCall();
        } catch (UnexpectedResponseException $e) {
            $response = $e->getResponse();

            if (!$response instanceof Response) {
                throw $e;
            }

            $this->registerClientResponse(null, $response);
            $this->lastException = $e;

            return;
        }

        $this->registerClientResponse($lastReturn, $api->getLastResponse());
    }

    /**
     * @return mixed
     */
    private function getLastReturn()
    {
        if ($this->lastException instanceof UnexpectedResponseException) {
            throw new RuntimeException(
                'There is no returned data, because the API call threw ' . get_class($this->lastException) . '.',
            );
        }

        return $this->lastReturn;
    }

    /**
     * @Then an UnexpectedResponseException was thrown
     */
    public function anUnexpectedResponseExceptionWasThrown(): void
    {
        TestCase::assertInstanceOf(UnexpectedResponseException::class, $this->lastException);

        $this->lastExceptionWasAsserted = true;
    }

    /**
     * @Then the response has the status code :statusCode
     */
    public function theResponseHasTheStatusCode(int $statusCode): void
    {
        TestCase::assertSame(
            $statusCode,
            $this->lastResponse->getStatusCode(),
            'Raw response content: ' . $this->lastResponse->getContent(),
        );
    }

    /**
     * @Then the response has the content type :contentType
     */
    public function theResponseHasTheContentType(string $contentType): void
    {
        TestCase::assertStringStartsWith(
            $contentType,
            $this->lastResponse->getContentType(),
            'Raw response content: ' . $this->lastResponse->getContent(),
        );
    }

    /**
     * @Then the response has an empty content type
     */
    public function theResponseHasAnEmptyContentType(): void
    {
        TestCase::assertSame('', $this->lastResponse->getContentType());
    }

    /**
     * @Then the response has the content :content
     */
    public function theResponseHasTheContent(string $content): void
    {
        TestCase::assertSame($content, $this->lastResponse->getContent());
    }

    /**
     * @Then the response has the content
     */
    public function theResponseHasTheContentWithMultipleLines(PyStringNode $string): void
    {
        TestCase::assertSame($string->getRaw(), $this->lastResponse->getContent());
    }

    /**
     * @Then the returned data is true
     */
    public function theReturnedDataIsTrue(): void
    {
        TestCase::assertTrue($this->getLastReturn());
    }

    /**
     * @Then the returned data is false
     */
    public function theReturnedDataIsFalse(): void
    {
        TestCase::assertFalse($this->getLastReturn());
    }

    /**
     * @Then the returned data is exactly :content
     */
    public function theReturnedDataIsExactly(string $content): void
    {
        TestCase::assertSame($content, $this->getLastReturn());
    }

    /**
     * @Then the returned data is exactly
     */
    public function theReturnedDataIsExactlyWithMultipleLines(PyStringNode $string): void
    {
        TestCase::assertSame($string->getRaw(), $this->getLastReturn());
    }

    /**
     * @Then the returned data is an instance of :className
     */
    public function theReturnedDataIsAnInstanceOf(string $className): void
    {
        TestCase::assertInstanceOf($className, $this->getLastReturn());
    }

    /**
     * @Then the returned data is an array
     */
    public function theReturnedDataIsAnArray(): void
    {
        TestCase::assertIsArray($this->getLastReturn());
    }

    /**
     * @Then the returned data contains :count items
     */
    public function theReturnedDataContainsItems(int $count): void
    {
        TestCase::assertCount($count, $this->getLastReturn());
    }

    /**
     * @Then the returned data contains the following data
     */
    public function theReturnedDataContainsTheFollowingData(TableNode $table): void
    {
        $returnData = $this->getLastReturn();

        if (! is_array($returnData)) {
            throw new RuntimeException('The returned data is not an array.');
        }

        $this->assertTableNodeIsSameAsArray($table, $returnData);
    }

    /**
     * @Then the returned data has only the following properties
     */
    public function theReturnedDataHasOnlyTheFollowingProperties(PyStringNode $string): void
    {
        $this->theReturnedDataPropertyHasOnlyTheFollowingProperties(null, $string);
    }

    /**
     * @Then the returned data has only the following properties with Redmine version :versionComparision
     */
    public function theReturnedDataHasOnlyTheFollowingPropertiesWithRedmineVersion(string $versionComparision, PyStringNode $string): void
    {
        $this->theReturnedDataPropertyHasOnlyTheFollowingPropertiesWithRedmineVersion(null, $versionComparision, $string);
    }

    /**
     * @Then the returned data has proterties with the following data
     */
    public function theReturnedDataHasProtertiesWithTheFollowingData(TableNode $table): void
    {
        $this->theReturnedDataPropertyContainsTheFollowingData(null, $table);
    }

    /**
     * @Then the returned data :property property is an array
     */
    public function theReturnedDataPropertyIsAnArray(?string $property): void
    {
        $returnData = $this->getLastReturnAsArray();

        $value = $this->getItemFromArray($returnData, $property);

        TestCase::assertIsArray($value);
    }

    /**
     * @Then the returned data :property property contains :count items
     */
    public function theReturnedDataPropertyContainsItems(?string $property, int $count): void
    {
        $returnData = $this->getLastReturnAsArray();

        $value = $this->getItemFromArray($returnData, $property);

        TestCase::assertIsArray($value);
        TestCase::assertCount($count, $value);
    }

    /**
     * @Then the returned data :property property contains the following data
     */
    public function theReturnedDataPropertyContainsTheFollowingData(?string $property, TableNode $table): void
    {
        $returnData = $this->getItemFromArray($this->getLastReturnAsArray(), $property);

        if (! is_array($returnData)) {
            throw new RuntimeException('The returned data on property "' . $property . '" is not an array.');
        }

        $this->assertTableNodeIsSameAsArray($table, $returnData);
    }

    /**
     * @Then the returned data :property property contains the following data with Redmine version :versionComparision
     */
    public function theReturnedDataPropertyContainsTheFollowingDataWithRedmineVersion(?string $property, string $versionComparision, TableNode $table): void
    {
        if ($this->isVersionMatch($versionComparision)) {
            $this->theReturnedDataPropertyContainsTheFollowingData($property, $table);
        }
    }

    /**
     * @Then the returned data :property property has only the following properties
     */
    public function theReturnedDataPropertyHasOnlyTheFollowingProperties(?string $property, PyStringNode $string): void
    {
        $value = $this->getItemFromArray($this->getLastReturnAsArray(), $property);

        $properties = array_keys($value);

        TestCase::assertSame($string->getStrings(), $properties);
    }

    /**
     * @Then the returned data :property property has only the following properties with Redmine version :versionComparision
     */
    public function theReturnedDataPropertyHasOnlyTheFollowingPropertiesWithRedmineVersion(?string $property, string $versionComparision, PyStringNode $string): void
    {
        if ($this->isVersionMatch($versionComparision)) {
            $this->theReturnedDataPropertyHasOnlyTheFollowingProperties($property, $string);
        }
    }

    /**
     * @return array<mixed>
     */
    private function getLastReturnAsArray(): array
    {
        if (isset($this->lastReturnAsArray)) {
            return $this->lastReturnAsArray;
        }

        $lastReturn = $this->getLastReturn();
        $returnData = null;

        if ($lastReturn instanceof SimpleXMLElement) {
            $returnData = json_decode(json_encode($lastReturn), true);
        } elseif (is_string($lastReturn)) {
            $returnData = json_decode($lastReturn, true);
        } elseif (is_array($lastReturn)) {
            $returnData = $lastReturn;
        }

        if (! is_array($returnData)) {
            throw new RuntimeException(sprintf(
                'the last returned data "%s" could not parsed into an array.',
                json_encode($lastReturn),
            ));
        }

        $this->lastReturnAsArray = $returnData;

        return $this->lastReturnAsArray;
    }

    /**
     * Get item from an array by key supporting "dot" notation.
     *
     * @param array<mixed> $array
     *
     * @return mixed
     */
    private function getItemFromArray(array $array, ?string $key)
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

    /**
     * @param array<mixed> $data
     */
    private function assertTableNodeIsSameAsArray(TableNode $table, array $data): void
    {
        foreach ($table as $row) {
            TestCase::assertArrayHasKey($row['property'], $data, 'Possible keys are: ' . implode(', ', array_keys($data)));

            $value = $data[$row['property']];

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

            // Handle placeholder %redmine_base_url%
            if (is_string($expected)) {
                $expected = str_replace('%redmine_base_url%', $this->redmine->getRedmineUrl(), $expected);
            }

            TestCase::assertSame($expected, $value, 'Error with property "' . $row['property'] . '"');
        }
    }

    private function isVersionMatch(string $versionComparision): bool
    {
        $parts = explode(' ', $versionComparision);

        if (count($parts) === 2) {
            if (!RedmineVersion::tryFrom($parts[1]) instanceof RedmineVersion) {
                throw new InvalidArgumentException('Comparison with Redmine "' . $versionComparision . '" is not supported.');
            }

            return version_compare($this->redmine->getVersionString(), $parts[1], $parts[0]);
        }

        if (count($parts) === 4) {
            if (!RedmineVersion::tryFrom($parts[1]) instanceof RedmineVersion) {
                throw new InvalidArgumentException('Comparison with Redmine "' . $versionComparision . '" is not supported.');
            }

            if (!RedmineVersion::tryFrom($parts[3]) instanceof RedmineVersion) {
                throw new InvalidArgumentException('Comparison with Redmine "' . $versionComparision . '" is not supported.');
            }

            return version_compare($this->redmine->getVersionString(), $parts[1], $parts[0])
                && version_compare($this->redmine->getVersionString(), $parts[3], $parts[2]);
        }

        throw new InvalidArgumentException('Comparison with Redmine "' . $versionComparision . '" is not supported.');
    }
}

<?php

declare(strict_types=1);

namespace Redmine\Tests\Fixtures;

use PHPUnit\Framework\TestCase;
use Redmine\Http\HttpClient;
use Redmine\Http\Request;
use Redmine\Http\Response;

/**
 * Asserting http client.
 *
 * The reqeust method of this client class can be configured with asserting requests and responses.
 */
final class AssertingHttpClient implements HttpClient
{
    public static function create(TestCase $testCase, array $dataSet, ...$dataSets): self
    {
        $dataSets = array_merge([$dataSet], $dataSets);

        /** @var \PHPUnit\Framework\MockObject\MockObject&HttpClient */
        $mock = $testCase->getMockBuilder(HttpClient::class)->getMock();
        $mock->expects($testCase->exactly(count($dataSets)))->method('request');

        $client = new self($testCase, $mock);

        foreach ($dataSets as $data) {
            $client->assertRequestData(...$data);
        }

        return $client;
    }

    private $testCase;
    private $client;
    private $fifoStack = [];

    private function __construct(TestCase $testCase, HttpClient $client)
    {
        $this->testCase = $testCase;
        $this->client = $client;
    }

    private function assertRequestData(
        string $method,
        string $path,
        string $contentType,
        string $content = '',
        int $responseCode = 200,
        string $responseContentType = '',
        string $responseContent = ''
    ) {
        if ($responseContentType === '') {
            $responseContentType = $contentType;
        }

        array_push($this->fifoStack, [
            'method' => $method,
            'path' => $path,
            'contentType' => $contentType,
            'content' => $content,
            'responseCode' => $responseCode,
            'responseContentType' => $responseContentType,
            'responseContent' => $responseContent,
        ]);
    }

    public function request(Request $request): Response
    {
        $this->client->request($request);

        $data = array_shift($this->fifoStack);

        if (! is_array($data)) {
            throw new \Exception(sprintf(
                'Mssing request data for Request "%s %s" with Content-Type "%s".',
                $request->getMethod(),
                $request->getPath(),
                $request->getContentType()
            ));
        }

        $this->testCase->assertSame($data['method'], $request->getMethod());
        $this->testCase->assertSame($data['path'], $request->getPath());
        $this->testCase->assertSame($data['contentType'], $request->getContentType());

        if ($data['content'] !== '' && $data['contentType'] === 'application/xml') {
            $this->testCase->assertXmlStringEqualsXmlString($data['content'], $request->getContent());
        } elseif ($data['content'] !== '' && $data['contentType'] === 'application/json') {
            $this->testCase->assertJsonStringEqualsJsonString($data['content'], $request->getContent());
        } else {
            $this->testCase->assertSame($data['content'], $request->getContent());
        }

        /** @var \PHPUnit\Framework\MockObject\MockObject&Response */
        $response = $this->testCase->getMockBuilder(Response::class)->getMock();

        $response->method('getStatusCode')->willReturn($data['responseCode']);
        $response->method('getContentType')->willReturn($data['responseContentType']);
        $response->method('getContent')->willReturn($data['responseContent']);

        return $response;
    }
}

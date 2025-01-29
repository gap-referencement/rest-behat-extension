<?php

namespace AllManager\RestBehatExtension\Tests\Units\Rest;

use AllManager\RestBehatExtension\Json\JsonStorage;
use AllManager\RestBehatExtension\Rest\ApiBrowser as SUT;
use GuzzleHttp\Psr7\Response;
use Http\Discovery\Psr17Factory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;

class ApiBrowserTest extends TestCase
{
    #[DataProvider('addHeaderDataProvider')]
    public function testAddRequestHeader(array $addHeadersSteps, array $expectedHeaders): void
    {
        $httpClient = $this->mockHttpClient(200);
        $sut = new SUT(new Psr17Factory(), new JsonStorage(), 'http://allmanager.com', $httpClient);

        foreach ($addHeadersSteps as $addHeadersStep) {
            foreach ($addHeadersStep as $headerName => $headerValue) {
                $sut->addRequestHeader($headerName, $headerValue);
            }
        }

        $this->assertEquals($expectedHeaders, $sut->getRequestHeaders());
    }

    public static function addHeaderDataProvider(): array
    {
        return [
            [[], []],
            [[['name' => 'value']], ['name' => 'value']],
            [[['name' => 'value'], ['name' => 'value2']], ['name' => 'value, value2']],
        ];
    }

    #[DataProvider('setHeaderDataProvider')]
    public function testSetRequestHeader(array $setHeadersSteps, array $expectedHeaders): void
    {
        $httpClient = $this->mockHttpClient(200);
        $sut = new SUT(new Psr17Factory(), new JsonStorage(), 'http://allmanager.com', $httpClient);

        foreach ($setHeadersSteps as $setHeadersStep) {
            foreach ($setHeadersStep as $headerName => $headerValue) {
                $sut->setRequestHeader($headerName, $headerValue);
            }
        }

        $this->assertEquals($expectedHeaders, $sut->getRequestHeaders());
    }

    public static function setHeaderDataProvider(): array
    {
        return [
            [[], []],
            [[['name' => 'value']], ['name' => 'value']],
            [[['name' => 'value'], ['name' => 'value2']], ['name' => 'value2']],
        ];
    }

    #[DataProvider('urlWithSlashesProvider')]
    public function testCreateRequestWithSlashesToClean(string $baseUrl, string $stepUrl, string $expectedUrl): void
    {
        $httpClient = $this->mockHttpClient(200);
        $sut = new SUT(new Psr17Factory(), new JsonStorage(), $baseUrl, $httpClient);

        $sut->sendRequest('GET', $stepUrl);

        $request = $sut->getRequest();

        $this->assertEquals($expectedUrl, (string) $request->getUri());
    }

    public static function urlWithSlashesProvider(): array
    {
        return [
            [
                'baseUrl' => 'http://allmanager.com/',
                'stepUrl' => '/contact/',
                'expectedUrl' => 'http://allmanager.com/contact/',
            ],
            [
                'baseUrl' => 'http://allmanager.com',
                'stepUrl' => '/contact/',
                'expectedUrl' => 'http://allmanager.com/contact/',
            ],
            [
                'baseUrl' => 'http://allmanager.com/',
                'stepUrl' => 'contact/',
                'expectedUrl' => 'http://allmanager.com/contact/',
            ],
            [
                'baseUrl' => 'http://allmanager.com',
                'stepUrl' => 'contact/',
                'expectedUrl' => 'http://allmanager.com/contact/',
            ],
        ];
    }

    #[DataProvider('responseDataProvider')]
    public function testGetReturnTheResponseWeExpected(int $statusCode, array $responseHeaders): void
    {
        $httpClient = $this->mockHttpClient($statusCode, null, $responseHeaders);
        $sut = new SUT(new Psr17Factory(), new JsonStorage(), 'http://allmanager.com', $httpClient);

        $sut->sendRequest('GET', 'http://allmanager.com/');

        $response = $sut->getResponse();
        $intersect = array_intersect_key($responseHeaders, $response->getHeaders());

        $this->assertEquals($responseHeaders, $intersect);
    }

    public static function responseDataProvider(): array
    {
        return [
            [
                'statusCode' => 200,
                'responseHeaders' => [
                    'name' => 'value',
                ],
            ],
            [
                'statusCode' => 400,
                'responseHeaders' => [
                    'name1' => 'value1',
                    'name2' => 'value2',
                ],
            ],
        ];
    }

    private function mockHttpClient(int $statusCode, ?string $body = null, array $headers = []): ClientInterface
    {
        $mockClient = $this->createMock(ClientInterface::class);
        $mockResponse = new Response($statusCode, $headers, $body);

        $mockClient->method('sendRequest')->willReturn($mockResponse);

        return $mockClient;
    }
}

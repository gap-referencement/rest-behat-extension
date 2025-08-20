<?php

namespace AllManager\RestBehatExtension\Rest;

use AllManager\RestBehatExtension\Html\Form;
use AllManager\RestBehatExtension\Json\JsonStorage;
use Http\Discovery\Psr17Factory;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class ApiBrowser
{
    private RequestInterface $request;
    private ResponseInterface $response;
    private array $requestHeaders = [];

    public function __construct(
        private Psr17Factory $messageFactory,
        private JsonStorage $responseStorage,
        private string $host,
        private ?ClientInterface $httpClient = null,
    ) {
        $this->httpClient = $httpClient ?: Psr18ClientDiscovery::find();
    }

    public function useHttpClient(ClientInterface $httpClient): void
    {
        $this->httpClient = $httpClient;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function getRequestHeaders(): array
    {
        return $this->requestHeaders;
    }

    public function sendRequest(
        string $method,
        string $uri,
        array|string|null $body = null,
    ): void {
        if (false === $this->hasHost($uri)) {
            $uri = mb_rtrim($this->host, '/').'/'.mb_ltrim($uri, '/');
        }

        if (is_array($body)) {
            $html = new Form($body);
            $body = $html->getBody();
            $this->setRequestHeader('Content-Type', $html->getContentTypeHeaderValue());
        }

        $this->request = $this->messageFactory->createRequest($method, $uri);
        foreach ($this->requestHeaders as $keyHeader => $valueHeader) {
            $this->request = $this->request->withHeader($keyHeader, $valueHeader);
        }
        if (null !== $body) {
            $this->request = $this->request->withBody($this->messageFactory->createStream($body));
        }

        $this->response = $this->httpClient->sendRequest($this->request);
        $this->requestHeaders = [];

        $this->responseStorage->writeRawContent((string) $this->response->getBody());
    }

    public function setRequestHeader(
        string $name,
        string $value
    ): void {
        $this->removeRequestHeader($name);
        $this->addRequestHeader($name, $value);
    }

    public function addRequestHeader(
        string $name,
        string $value,
    ): void {
        $name = mb_strtolower($name);
        if (isset($this->requestHeaders[$name])) {
            $this->requestHeaders[$name] .= ', '.$value;
        } else {
            $this->requestHeaders[$name] = $value;
        }
    }

    private function removeRequestHeader(string $headerName): void
    {
        $headerName = mb_strtolower($headerName);
        if (array_key_exists($headerName, $this->requestHeaders)) {
            unset($this->requestHeaders[$headerName]);
        }
    }

    private function hasHost(string $uri): bool
    {
        return str_contains($uri, '://');
    }
}

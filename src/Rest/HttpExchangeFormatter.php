<?php

namespace AllManager\RestBehatExtension\Rest;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

readonly class HttpExchangeFormatter
{
    public function __construct(
        private RequestInterface $request,
        private ?ResponseInterface $response = null,
    ) {
    }

    public function formatRequest(): string
    {
        return sprintf(
            "%s %s :\n%s%s\n",
            $this->request->getMethod(),
            $this->request->getUri(),
            $this->getRawHeaders($this->request->getHeaders()),
            $this->request->getBody()
        );
    }

    public function formatFullExchange(): string
    {
        if (null === $this->response) {
            throw new \LogicException('You should send a request and store its response before printing them.');
        }

        return sprintf(
            "%s %s :\n%s %s\n%s%s\n",
            $this->request->getMethod(),
            $this->request->getUri()->__toString(),
            $this->response->getStatusCode(),
            $this->response->getReasonPhrase(),
            $this->getRawHeaders($this->response->getHeaders()),
            $this->response->getBody()
        );
    }

    private function getRawHeaders(array $headers): string
    {
        $rawHeaders = '';
        foreach ($headers as $key => $value) {
            $rawHeaders .= sprintf("%s: %s\n", $key, is_array($value) ? implode(', ', $value) : $value);
        }
        $rawHeaders .= "\n";

        return $rawHeaders;
    }
}

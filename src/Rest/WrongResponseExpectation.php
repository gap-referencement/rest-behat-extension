<?php

namespace AllManager\RestBehatExtension\Rest;

use AllManager\RestBehatExtension\ExpectationFailed;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class WrongResponseExpectation extends ExpectationFailed
{
    public function __construct(
        string $message,
        private RequestInterface $request,
        private ResponseInterface $response,
        ?\Exception $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function getContextText(): string
    {
        $formatter = new HttpExchangeFormatter($this->request, $this->response);

        return $formatter->formatFullExchange();
    }
}

<?php

namespace AllManager\RestBehatExtension\OpenApi;

use AllManager\RestBehatExtension\ExpectationFailed;
use League\OpenAPIValidation\Schema\Exception\SchemaMismatch;
use Psr\Http\Message\ResponseInterface;

class OpenAPIExpectationFailed extends ExpectationFailed
{
    public function __construct(
        string $method,
        string $path,
        \Exception $previous,
        private readonly ResponseInterface $response,
    ) {
        $description = '';
        $schemaException = $previous->getPrevious();
        if ($schemaException instanceof SchemaMismatch) {
            $description = sprintf(
                "%s\n%s",
                implode(' -> ', $schemaException->dataBreadCrumb()?->buildChain() ?? []),
                $schemaException->getMessage()
            );
        }

        parent::__construct(
            sprintf(
                "The answer doesn’t match OpenAPI schema for %s %s:\n%s\n\n%s",
                mb_strtoupper($method),
                $path,
                $previous->getMessage(),
                $description,
            ),
            0,
            $previous,
        );
    }

    public function getContextText(): false|string
    {
        return json_encode(json_decode($this->response->getBody(), flags: JSON_THROW_ON_ERROR), JSON_PRETTY_PRINT);
    }
}

<?php

namespace AllManager\RestBehatExtension\OpenApi;

use AllManager\RestBehatExtension\Rest\ApiBrowser;
use Behat\Behat\Context\Context;
use Behat\Step\Then;
use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;

class OpenApiContext implements Context
{
    private ValidatorBuilder $validatorBuilder;
    private ApiBrowser $apiBrowser;

    public function initializeConfig(ApiBrowser $apiBrowser, string $openApiFilePath): void
    {
        $this->apiBrowser = $apiBrowser;
        if (!file_exists($openApiFilePath)) {
            throw new \RuntimeException(sprintf('OpenAPI file not found at %s', $openApiFilePath));
        }

        $this->validatorBuilder = (new ValidatorBuilder())
            ->fromJsonFile($openApiFilePath);
    }

    /**
     * @throws OpenAPIExpectationFailed
     */
    #[Then('the response should match OpenAPI specification for :method :path')]
    public function theResponseShouldMatchOpenAPISpecification(string $method, string $path): void
    {
        $response = $this->apiBrowser->getResponse();

        $responseValidator = $this->validatorBuilder->getResponseValidator();

        try {
            $operation = new OperationAddress($path, mb_strtolower($method));
            $responseValidator->validate($operation, $response);
        } catch (ValidationFailed $e) {
            throw new OpenAPIExpectationFailed(method: $method, path: $path, response: $response, previous: $e);
        }
    }

    /**
     * @throws OpenAPIExpectationFailed
     */
    #[Then('the response should match OpenAPI specification')]
    public function theResponseShouldMatchOpenAPISpecificationAuto(): void
    {
        $request = $this->apiBrowser->getRequest();

        $method = $request->getMethod();
        $path = $request->getUri()->getPath();

        /** @var string $parsedPath */
        $parsedPath = parse_url($path, PHP_URL_PATH);

        $this->theResponseShouldMatchOpenAPISpecification($method, $parsedPath);
    }
}

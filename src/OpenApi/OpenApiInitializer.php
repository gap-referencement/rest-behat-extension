<?php

namespace AllManager\RestBehatExtension\OpenApi;

use AllManager\RestBehatExtension\Rest\ApiBrowser;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;

final readonly class OpenApiInitializer implements ContextInitializer
{
    public function __construct(
        private ApiBrowser $apiBrowser,
        private string $openApiFilePath,
    ) {
    }

    public function initializeContext(Context $context): void
    {
        if (!$context instanceof OpenApiContext) {
            return;
        }

        $context->initializeConfig(
            apiBrowser: $this->apiBrowser,
            openApiFilePath: $this->openApiFilePath
        );
    }
}

<?php

namespace AllManager\RestBehatExtension\Tests\Units\OpenApi;

use AllManager\RestBehatExtension\OpenApi\OpenAPIExpectationFailed as SUT;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class OpenAPIExpectationFailedTest extends TestCase
{
    public function testItDisplayPrettyJsonResponseAsContext(): void
    {
        $response = new Response(200, ['Content-Type' => 'application/json'], '{"status":"ok"}');

        $exception = new SUT('get', '/foo', new \Exception('Mismatch'), $response);

        $this->assertSame(
            <<<'EOF'
                {
                    "status": "ok"
                }
                EOF,
            $exception->getContextText(),
        );
    }
}

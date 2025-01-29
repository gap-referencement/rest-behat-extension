<?php

namespace AllManager\RestBehatExtension\Tests\Units\Json;

use AllManager\RestBehatExtension\Rest\WrongResponseExpectation as SUT;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class WrongResponseExpectationTest extends TestCase
{
    public function testItDisplayPrettyResponseWhenCastToString(): void
    {
        $request = new Request('GET', 'http://test.com/foo');
        $response = new Response(200, ['Content-Type' => 'application/json'], '{"status":"ok"}');

        $wrongResponseExpectation = new SUT('Error', $request, $response);

        $this->assertStringContainsString(
            <<<'EOF'
                |  GET http://test.com/foo :
                |  200 OK
                |  Content-Type: application/json
                |  
                |  {"status":"ok"}
                EOF,
            (string) $wrongResponseExpectation,
        );
    }
}

<?php

namespace AllManager\RestBehatExtension\Tests\Units\Json;

use AllManager\RestBehatExtension\Json\Json;
use AllManager\RestBehatExtension\Json\WrongJsonExpectation as SUT;
use PHPUnit\Framework\TestCase;

final class WrongJsonExpectationTest extends TestCase
{
    public function testItDisplayPrettyJsonWhenCastToString(): void
    {
        $json = new Json('{"foo":"bar"}');
        $wrongJsonExpectation = new SUT('Error', $json);

        $this->assertStringContainsString(
            <<<'EOF'
                |  {
                |      "foo": "bar"
                |  }
                EOF,
            (string) $wrongJsonExpectation,
        );
    }
}

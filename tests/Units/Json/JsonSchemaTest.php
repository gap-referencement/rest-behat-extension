<?php

namespace AllManager\RestBehatExtension\Tests\Units\Json;

use AllManager\RestBehatExtension\Json\Json;
use AllManager\RestBehatExtension\Json\JsonSchema as SUT;
use JsonSchema\SchemaStorage;
use JsonSchema\Validator;
use PHPUnit\Framework\TestCase;

final class JsonSchemaTest extends TestCase
{
    public function testShouldValidateCorrectJson(): void
    {
        $json = new Json('{"name":"John","age":30,"email":"john@doe.com"}');
        $schema = new SUT(__DIR__.'/schema.json', new Validator(), new SchemaStorage());

        $this->assertTrue($schema->validate($json));
    }

    public function testShouldThrowExceptionForIncorrectJson(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('JSON does not validate. Violations:
  - [age] The property age is required
  - [email] The property email is required
');

        $json = new Json('{"name":"John"}');
        $schema = new SUT(__DIR__.'/schema.json', new Validator(), new SchemaStorage());

        $schema->validate($json);
    }
}

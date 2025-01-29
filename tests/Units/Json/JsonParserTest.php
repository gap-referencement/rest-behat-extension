<?php

namespace AllManager\RestBehatExtension\Tests\Units\Json;

use AllManager\RestBehatExtension\Json\Json;
use AllManager\RestBehatExtension\Json\JsonParser as SUT;
use AllManager\RestBehatExtension\Json\JsonSchema;
use JsonSchema\SchemaStorage;
use JsonSchema\Validator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccessor;

final class JsonParserTest extends TestCase
{
    public function testShouldReadJson(): void
    {
        $json = new Json('{"foo": {"bar": "baz"}}');
        $sut = new SUT(new PropertyAccessor());

        $result = $sut->evaluate($json, 'foo.bar');

        $this->assertEquals('baz', $result);
    }

    public function testShouldFailIfJsonReadingFail(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to evaluate expression "foo.foo"');

        $json = new Json('{"foo": {"bar": "baz"}}');
        $sut = new SUT(new PropertyAccessor());

        $sut->evaluate($json, 'foo.foo');
    }

    public function testShouldValidJsonThroughItsSchema(): void
    {
        $json = new Json('{"name":"John","age":30,"email":"john@doe.com"}');
        $schema = new JsonSchema(__DIR__.'/schema.json', new Validator(), new SchemaStorage());
        $sut = new SUT(new PropertyAccessor());

        $result = $sut->validate($json, $schema);

        $this->assertTrue($result);
    }

    public function testShouldFailJsonThroughItsSchema(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('JSON does not validate. Violations:
  - [age] The property age is required
  - [email] The property email is required
');

        $json = new Json('{"name":"John"}');
        $schema = new JsonSchema(__DIR__.'/schema.json', new Validator(), new SchemaStorage());
        $sut = new SUT(new PropertyAccessor());

        $sut->validate($json, $schema);
    }
}

<?php

namespace AllManager\RestBehatExtension\Tests\Units\Json;

use AllManager\RestBehatExtension\Json\Json as SUT;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccess;

final class JsonTest extends TestCase
{
    public function testShouldNotDecodeInvalidJson(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The string "{{json" is not valid json');

        new SUT('{{json');
    }

    public function testShouldDecodeValidJson(): void
    {
        try {
            $sut = new SUT('{"name": "John", "age": 30}');
            $this->assertInstanceOf(SUT::class, $sut);
        } catch (\Exception $e) {
            $this->fail('An exception was thrown when it should not have been: '.$e->getMessage());
        }
    }

    public function testShouldEncodeValidJson(): void
    {
        $content = '{"foo":"bar"}';
        $sut = new SUT($content);
        $this->assertEquals($content, (string) $sut);
    }

    public function testShouldNotReadInvalidExpression(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Can\'t get a way to read the property "email" in class "stdClass".');

        $jsonData = '{"name": "John", "age": 30}';
        $sut = new SUT($jsonData);
        $accessor = PropertyAccess::createPropertyAccessor();

        $sut->read('email', $accessor);
    }

    public function testShouldReadValidExpression(): void
    {
        $sut = new SUT('{"person": {"name": "John", "age": 30}}');
        $accessor = PropertyAccess::createPropertyAccessor();

        $result = $sut->read('person.name', $accessor);

        $this->assertEquals('John', $result);
    }
}

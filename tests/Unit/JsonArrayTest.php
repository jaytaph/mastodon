<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Exception\IncorrectDataTypeException;
use App\Exception\InvalidIndexException;
use App\JsonArray;
use PHPUnit\Framework\TestCase;

class JsonArrayTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $jsonArray = $this->createJsonArray();

        $this->assertEquals('def', $jsonArray->getString('[not-exists]', 'def'));
        $this->assertEquals('foo', $jsonArray->getString('[level0.1]', 'def'));

        $this->assertEquals('def', $jsonArray->getString('[level0.4][not-exists]', 'def'));
        $this->assertEquals('bar', $jsonArray->getString('[level0.4][level1.1]', 'def'));

        $a = new JsonArray(['level2.1' => 'baz', 'level2.2' => 2, 'level2.3' => true, 'level2.5' => null]);
        $b = new JsonArray(['foo', 'bar']);
        $this->assertEquals($a, $jsonArray->getJsonArray('[level0.4][not-exists]', $a));
        $this->assertEquals($a, $jsonArray->getJsonArray('[level0.4][level1.4]', $b));
        $this->assertEquals($b, $jsonArray->getJsonArray('[level0.4][notexists]', $b));

        $this->assertEquals(123, $jsonArray->getInt('[not-exists]', 123));
    }

    public function testGetString(): void
    {
        $jsonArray = $this->createJsonArray();

        $this->assertIsString($jsonArray->getString('[level0.4][level1.4][level2.1]'));
        $this->assertEquals('baz', $jsonArray->getString('[level0.4][level1.4][level2.1]'));
        $this->assertNull($jsonArray->getStringOrNull('[doesNotExist]'));
        $this->assertNotNull($jsonArray->getStringOrNull('[level0.1]'));


        $jsonArray->getString('[level0.4][level1.4][level2.5]', 'baz');
    }

    public function testGetInt(): void
    {
        $jsonArray = $this->createJsonArray();

        $this->assertIsInt($jsonArray->getInt('[level0.2]'));
        $this->assertEquals(0, $jsonArray->getInt('[level0.2]'));

        $this->assertNull($jsonArray->getIntOrNull('[doesNotExist]'));
        $this->assertNotNull($jsonArray->getIntOrNull('[level0.4][level1.2]'));
    }

    public function testIntNotFoundThrowException(): void
    {
        $jsonArray = $this->createJsonArray();

        $this->expectException(InvalidIndexException::class);
        $this->expectExceptionMessage('Invalid index: "[doesNotExist]"');
        $jsonArray->getInt('[doesNotExist]');
    }

    public function testIntWithDefaultValue(): void
    {
        $jsonArray = $this->createJsonArray();

        $this->assertEquals(123, $jsonArray->getInt('[doesNotExist]', 123));
    }

    public function testBool(): void
    {
        $jsonArray = $this->createJsonArray();

        $this->assertIsBool($jsonArray->getBool('[level0.3]'));
        $this->assertFalse($jsonArray->getBool('[level0.3]'));
        $this->assertFalse($jsonArray->getBool('[level0.4][level1.3]'));
        $this->assertTrue($jsonArray->getBool('[level0.4][level1.4][level2.3]'));
    }

    public function testEmpty(): void
    {
        $jsonArray = $this->createJsonArray();
        $this->assertFalse($jsonArray->isEmpty());

        $jsonArray = JsonArray::empty();
        $this->assertTrue($jsonArray->isEmpty());
    }

    public function testExists(): void
    {
        $jsonArray = $this->createJsonArray();
        $this->assertTrue($jsonArray->exists('[level0.4][level1.4][level2.1]'));
        $this->assertTrue($jsonArray->exists('[level0.4][level1.4]'));
        $this->assertTrue($jsonArray->exists('[level0.4]'));
        $this->assertFalse($jsonArray->exists('doesnotexist'));
        $this->assertFalse($jsonArray->exists('[doesnotexist]'));


        $this->assertTrue($jsonArray->isNullOrNotExists('[level0.5]'));
        $this->assertTrue($jsonArray->isNullOrNotExists('[notexists]'));
        $this->assertFalse($jsonArray->isNullOrNotExists('[level0.1]'));
    }

    public function testIsJsonArray(): void
    {
        $jsonArray = $this->createJsonArray();

        $this->assertTrue($jsonArray->isJsonArray('[level0.4][level1.4]'));
        $this->assertTrue($jsonArray->isJsonArray('[level0.4]'));
        $this->assertFalse($jsonArray->isJsonArray('[level0.1]'));
    }

    public function testJsonSerialize() {
        $jsonArray = $this->createJsonArray();
        $this->assertEquals($jsonArray->toArray(), $jsonArray->jsonSerialize());
    }

    public function testStringException(): void
    {
        $jsonArray = $this->createJsonArray();

        $this->expectException(IncorrectDataTypeException::class);
        $jsonArray->getString('[level0.2]');
    }

    public function testJsonArray(): void
    {
        $jsonArray = $this->createJsonArray();

        $this->assertNull($jsonArray->getJsonArrayOrNull('[doesNotExist]'));
        $this->assertNotNull($jsonArray->getJsonArrayOrNull('[level0.4]'));

        $this->expectException(IncorrectDataTypeException::class);
        $jsonArray->getJsonArrayOrNull('[level0.1]');
    }

    public function testStringOrNullException(): void
    {
        $jsonArray = $this->createJsonArray();

        $this->expectException(IncorrectDataTypeException::class);
        $jsonArray->getStringOrNull('[level0.2]');
    }

    public function testIntException(): void
    {
        $jsonArray = $this->createJsonArray();

        $this->expectException(IncorrectDataTypeException::class);
        $jsonArray->getInt('[level0.1]');
    }

    public function testIntOrNullException(): void
    {
        $jsonArray = $this->createJsonArray();

        $this->expectException(IncorrectDataTypeException::class);
        $jsonArray->getIntOrNull('[level0.1]');
    }

    public function testBoolException(): void
    {
        $jsonArray = $this->createJsonArray();

        $this->expectException(IncorrectDataTypeException::class);
        $jsonArray->getBool('[level0.1]');
    }

    public function testJsonArrayException(): void
    {
        $jsonArray = $this->createJsonArray();

        $this->expectException(IncorrectDataTypeException::class);
        $jsonArray->getJsonArray('[level0.1]');
    }

    public function testFromJson(): void
    {
        $jsonArray = JsonArray::fromJson('{"foo": "bar"}');
        $this->assertEquals('bar', $jsonArray->getString('[foo]'));

        $jsonArray = JsonArray::fromJson('"foo"');
        $this->assertEquals('foo', $jsonArray->getString('[0]'));

        $this->expectException(\JsonException::class);
        JsonArray::fromJson('aap');
    }

    protected function createJsonArray(): JsonArray
    {
        $data = [
            'level0.1' => 'foo',
            'level0.2' => 0,
            'level0.3' => false,
            'level0.4' => [
                'level1.1' => 'bar',
                'level1.2' => 1,
                'level1.3' => false,
                'level1.4' => [
                    'level2.1' => 'baz',
                    'level2.2' => 2,
                    'level2.3' => true,
                    'level2.5' => null,
                ],
                'level1.5' => null,
            ],
            'level0.5' => null,
        ];

        return new JsonArray($data);
    }
}

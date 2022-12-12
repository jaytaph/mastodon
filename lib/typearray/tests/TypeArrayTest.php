<?php

declare(strict_types=1);

namespace Tests;

use Jaytaph\TypeArray\Exception\IncorrectDataTypeException;
use Jaytaph\TypeArray\Exception\InvalidIndexException;
use Jaytaph\TypeArray\TypeArray;
use PHPUnit\Framework\TestCase;

class TypeArrayTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $typeArray = $this->createTypeArray();

        $this->assertEquals('def', $typeArray->getString('[not-exists]', 'def'));
        $this->assertEquals('foo', $typeArray->getString('[level0.1]', 'def'));

        $this->assertEquals('def', $typeArray->getString('[level0.4][not-exists]', 'def'));
        $this->assertEquals('bar', $typeArray->getString('[level0.4][level1.1]', 'def'));

        $a = new TypeArray(['level2.1' => 'baz', 'level2.2' => 2, 'level2.3' => true, 'level2.5' => null]);
        $b = new TypeArray(['foo', 'bar']);
        $this->assertEquals($a, $typeArray->getTypeArray('[level0.4][not-exists]', $a));
        $this->assertEquals($a, $typeArray->getTypeArray('[level0.4][level1.4]', $b));
        $this->assertEquals($b, $typeArray->getTypeArray('[level0.4][notexists]', $b));

        $this->assertEquals(123, $typeArray->getInt('[not-exists]', 123));
    }

    public function testGetString(): void
    {
        $typeArray = $this->createTypeArray();

        $this->assertIsString($typeArray->getString('[level0.4][level1.4][level2.1]'));
        $this->assertEquals('baz', $typeArray->getString('[level0.4][level1.4][level2.1]'));
        $this->assertNull($typeArray->getStringOrNull('[doesNotExist]'));
        $this->assertNotNull($typeArray->getStringOrNull('[level0.1]'));


        $typeArray->getString('[level0.4][level1.4][level2.5]', 'baz');
    }

    public function testGetInt(): void
    {
        $typeArray = $this->createTypeArray();

        $this->assertIsInt($typeArray->getInt('[level0.2]'));
        $this->assertEquals(0, $typeArray->getInt('[level0.2]'));

        $this->assertNull($typeArray->getIntOrNull('[doesNotExist]'));
        $this->assertNotNull($typeArray->getIntOrNull('[level0.4][level1.2]'));
    }

    public function testIntNotFoundThrowException(): void
    {
        $typeArray = $this->createTypeArray();

        $this->expectException(InvalidIndexException::class);
        $this->expectExceptionMessage('Invalid index: "[doesNotExist]"');
        $typeArray->getInt('[doesNotExist]');
    }

    public function testIntWithDefaultValue(): void
    {
        $typeArray = $this->createTypeArray();

        $this->assertEquals(123, $typeArray->getInt('[doesNotExist]', 123));
    }

    public function testBool(): void
    {
        $typeArray = $this->createTypeArray();

        $this->assertIsBool($typeArray->getBool('[level0.3]'));
        $this->assertFalse($typeArray->getBool('[level0.3]'));
        $this->assertFalse($typeArray->getBool('[level0.4][level1.3]'));
        $this->assertTrue($typeArray->getBool('[level0.4][level1.4][level2.3]'));
    }

    public function testEmpty(): void
    {
        $typeArray = $this->createTypeArray();
        $this->assertFalse($typeArray->isEmpty());

        $typeArray = TypeArray::empty();
        $this->assertTrue($typeArray->isEmpty());
    }

    public function testExists(): void
    {
        $typeArray = $this->createTypeArray();
        $this->assertTrue($typeArray->exists('[level0.4][level1.4][level2.1]'));
        $this->assertTrue($typeArray->exists('[level0.4][level1.4]'));
        $this->assertTrue($typeArray->exists('[level0.4]'));
        $this->assertFalse($typeArray->exists('doesnotexist'));
        $this->assertFalse($typeArray->exists('[doesnotexist]'));


        $this->assertTrue($typeArray->isNullOrNotExists('[level0.5]'));
        $this->assertTrue($typeArray->isNullOrNotExists('[notexists]'));
        $this->assertFalse($typeArray->isNullOrNotExists('[level0.1]'));
    }

    public function testIsTypeArray(): void
    {
        $typeArray = $this->createTypeArray();

        $this->assertTrue($typeArray->isTypeArray('[level0.4][level1.4]'));
        $this->assertTrue($typeArray->isTypeArray('[level0.4]'));
        $this->assertFalse($typeArray->isTypeArray('[level0.1]'));
    }

    public function testJsonSerialize()
    {
        $typeArray = $this->createTypeArray();
        $this->assertEquals($typeArray->toArray(), $typeArray->jsonSerialize());
    }

    public function testStringException(): void
    {
        $typeArray = $this->createTypeArray();

        $this->expectException(IncorrectDataTypeException::class);
        $typeArray->getString('[level0.2]');
    }

    public function testTypeArray(): void
    {
        $typeArray = $this->createTypeArray();

        $this->assertNull($typeArray->getTypeArrayOrNull('[doesNotExist]'));
        $this->assertNotNull($typeArray->getTypeArrayOrNull('[level0.4]'));

        $this->expectException(IncorrectDataTypeException::class);
        $typeArray->getTypeArrayOrNull('[level0.1]');
    }

    public function testStringOrNullException(): void
    {
        $typeArray = $this->createTypeArray();

        $this->expectException(IncorrectDataTypeException::class);
        $typeArray->getStringOrNull('[level0.2]');
    }

    public function testIntException(): void
    {
        $typeArray = $this->createTypeArray();

        $this->expectException(IncorrectDataTypeException::class);
        $typeArray->getInt('[level0.1]');
    }

    public function testIntOrNullException(): void
    {
        $typeArray = $this->createTypeArray();

        $this->expectException(IncorrectDataTypeException::class);
        $typeArray->getIntOrNull('[level0.1]');
    }

    public function testBoolException(): void
    {
        $typeArray = $this->createTypeArray();

        $this->expectException(IncorrectDataTypeException::class);
        $typeArray->getBool('[level0.1]');
    }

    public function testTypeArrayException(): void
    {
        $typeArray = $this->createTypeArray();

        $this->expectException(IncorrectDataTypeException::class);
        $typeArray->getTypeArray('[level0.1]');
    }

    public function testFromJson(): void
    {
        $typeArray = TypeArray::fromJson('{"foo": "bar"}');
        $this->assertEquals('bar', $typeArray->getString('[foo]'));

        $typeArray = TypeArray::fromJson('"foo"');
        $this->assertEquals('foo', $typeArray->getString('[0]'));

        $this->expectException(\JsonException::class);
        TypeArray::fromJson('aap');
    }

    protected function createTypeArray(): TypeArray
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

        return new TypeArray($data);
    }
}

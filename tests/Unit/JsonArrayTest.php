<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\JsonArray;
use PHPUnit\Framework\TestCase;

class JsonArrayTest extends TestCase
{
    public function testJsonArray(): void
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
                ],
            ]
        ];

        $jsonArray = new JsonArray($data);

        $this->assertIsInt($jsonArray->getInt('[level0.2]'));
        $this->assertEquals(0, $jsonArray->getInt('[level0.2]'));

        $this->assertIsString($jsonArray->getString('[level0.4][level1.4][level2.1]'));
        $this->assertEquals('baz', $jsonArray->getString('[level0.4][level1.4][level2.1]'));

        $this->assertNull($jsonArray->getStringIfExists('[doesNotExist]'));
        $this->assertNull($jsonArray->getIntIfExists('[doesNotExist]'));

        $this->assertNotNull($jsonArray->getStringIfExists('[level0.1]'));

    }
}

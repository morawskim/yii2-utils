<?php

namespace mmo\yii2\tests\helpers;

use mmo\yii2\helpers\ArrayHelper;

class ArrayHelperTest extends \mmo\yii2\tests\TestCase
{
    public function testFillToMultiplyIfLess(): void
    {
        $data = ['a', 'b', 'c'];
        $newArray = ArrayHelper::fillToMultiply($data, 4);
        $this->assertCount(4, $newArray);
        $this->assertNull($newArray[3]);
    }

    public function testFillToMultiplyIfNumberOfElementsIsGreaterThanChunkSize(): void
    {
        $data = ['a', 'b', 'c', 'd', 'e'];
        $newArray = ArrayHelper::fillToMultiply($data, 4);
        $this->assertCount(8, $newArray);
        $this->assertNull($newArray[5]);
        $this->assertNull($newArray[6]);
        $this->assertNull($newArray[7]);
    }

    public function testFillToMultiplyIfChunkSizeEqualArrayLength(): void
    {
        $element1 = 'a';
        $element2 = 'b';
        $element3 = 'c';
        $element4 = 'd';
        $data = [$element1, $element2, $element3, $element4];
        $newArray = ArrayHelper::fillToMultiply($data, 4);
        $this->assertCount(4, $newArray);
        $this->assertEquals($data[0], $element1);
        $this->assertEquals($data[1], $element2);
        $this->assertEquals($data[2], $element3);
        $this->assertEquals($data[3], $element4);
    }
}

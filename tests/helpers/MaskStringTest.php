<?php

namespace mmo\yii2\tests\helpers;

use mmo\yii2\helpers\MaskString;

/**
 * @requires extension mbstring
 */
class MaskStringTest extends \mmo\yii2\tests\TestCase
{
    /**
     * @dataProvider providerForScrambleValue
     */
    public function testScrambleValue(string $value, string $expect): void
    {
        $scramble = new MaskString();
        $mask = $scramble->scrambleValue($value);
        $this->assertEquals($expect, $mask);
    }

    public function providerForScrambleValue(): array
    {
        return [
            ['1234567', '1*****7'],
            ['', ''],
            ['1', '1'],
            ['12', '12'],
        ];
    }
}

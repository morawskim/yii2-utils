<?php

namespace mmo\yii2\helpers;

use yii\helpers\BaseArrayHelper;

class ArrayHelper extends BaseArrayHelper
{
    /**
     * @param array $array
     * @param int $multiply
     * @param null|mixed $newValue
     * @return array
     */
    public static function fillToMultiply(array $array, int $multiply, $newValue = null): array
    {
        $count = count($array);

        $remainder = $count % $multiply;
        if ($remainder != 0) {
            $i = $multiply - $remainder;
            while ($i > 0) {
                $array[] = $newValue;
                $i--;
            }
        }

        return $array;
    }
}

<?php

namespace mmo\yii2\helpers;

class MaskString
{
    public function __construct()
    {
        if (!extension_loaded('mbstring')) {
            throw new \RuntimeException('This class require mbstring extension');
        }
    }

    public function scrambleValue($word): string
    {
        if (strlen($word) < 2) {
            return $word;
        }
        $prefix = mb_substr($word, 0, 1);
        $stringToMask = mb_substr($word, 1, -1);
        $suffix = mb_substr($word, -1);
        return $prefix . $this->hideText($stringToMask) . $suffix;
    }

    protected function hideText(string $string): string
    {
        $result = '';
        $length = strlen($string);
        for ($i = 0; $i < $length; $i++) {
            $result .= '*';
        }
        return $result;
    }
}

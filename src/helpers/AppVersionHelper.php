<?php

namespace mmo\yii2\helpers;

use mmo\yii2\models\AppVersion;

class AppVersionHelper
{
    public static function factoryFromFile(string $filePath): AppVersion
    {
        if (!is_file($filePath)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not file', $filePath));
        }
        if (!is_readable($filePath)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not readable', $filePath));
        }
        $data = static::processFile($filePath);
        return new AppVersion($data);
    }

    private static function processFile(string $filePath): array
    {
        $map = [];
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            [$variable, $value] = explode('=', $line, 2);
            $map[$variable] = $value;
        }
        return $map;
    }
}

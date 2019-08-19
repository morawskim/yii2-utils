<?php

namespace mmo\yii2\models;

class AppVersion
{
    public const FIELD_VERSION = 'VERSION';
    public const FIELD_COMMIT = 'COMMIT';

    /** @var array */
    private $map = [];

    public function __construct(array $data)
    {
        $this->map = $data;
    }

    public function getVersion(string $defaultValue = 'UNKNOWN', string $fieldName = self::FIELD_VERSION): string
    {
        return $this->getFieldValue($fieldName, $defaultValue);
    }

    public function getCommit(string $defaultValue = 'UNKNOWN', string $fieldName = self::FIELD_COMMIT): string
    {
        return $this->getFieldValue($fieldName, $defaultValue);
    }

    public function getFieldValue(string $fieldName, string $defaultValue): string
    {
        return $this->map[$fieldName] ?? $defaultValue;
    }

    public function hasField(string $fieldName): bool
    {
        return array_key_exists($fieldName, $this->map);
    }
}

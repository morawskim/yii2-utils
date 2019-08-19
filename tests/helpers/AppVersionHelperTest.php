<?php

namespace mmo\yii2\tests\helpers;

use mmo\yii2\helpers\AppVersionHelper;

class AppVersionHelperTest extends \mmo\yii2\tests\TestCase
{
    public function testFactoryFromFile()
    {
        $obj = AppVersionHelper::factoryFromFile(__DIR__ . '/../_files/appVersionHelper.txt');
        $this->assertEquals('0.1.0-SNAPSHOT', $obj->getFieldValue('VERSION', '??'));
        $this->assertEquals('value', $obj->getFieldValue('CUSTOM_FIELD', '??'));
        $this->assertEquals('value=test', $obj->getFieldValue('EXTRA_EQUAL_CHAR', '??'));
    }
}

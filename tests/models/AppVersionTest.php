<?php

namespace mmo\yii2\tests\models;

use mmo\yii2\models\AppVersion;

class AppVersionTest extends \mmo\yii2\tests\TestCase
{
    public function testGetters()
    {
        $version = '0.1.0';
        $commit = '03cfd743661f07975fa2f1220c5194cbaff48451';
        $obj = new AppVersion([
            AppVersion::FIELD_VERSION => $version,
            AppVersion::FIELD_COMMIT => $commit,
        ]);
        $this->assertEquals($version, $obj->getVersion());
        $this->assertEquals($commit, $obj->getCommit());
        $this->assertEquals($commit, $obj->getFieldValue(AppVersion::FIELD_COMMIT, 'UNKNOWN'));
        $this->assertEquals($version, $obj->getFieldValue(AppVersion::FIELD_VERSION, 'UNKNOWN'));
    }

    public function testDefaultValues()
    {
        $obj = new AppVersion([]);
        $this->assertEquals('UNKNOWN', $obj->getVersion());
        $this->assertEquals('UNKNOWN', $obj->getCommit());
        $this->assertEquals('??', $obj->getFieldValue(AppVersion::FIELD_COMMIT, '??'));
        $this->assertEquals('??', $obj->getFieldValue(AppVersion::FIELD_VERSION, '??'));
    }

    public function testHasField()
    {
        $obj = new AppVersion(['FIELD' => 'VALUE']);
        $this->assertFalse($obj->hasField(AppVersion::FIELD_VERSION));
        $this->assertTrue($obj->hasField('FIELD'));
    }
}

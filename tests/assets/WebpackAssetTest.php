<?php

namespace mmo\yii2\tests\assets;

use mmo\yii2\assets\WebpackAsset;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\web\AssetBundle;
use yii\web\AssetManager;
use yii\web\View;

class WebpackAssetTest extends \mmo\yii2\tests\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockApplication();
        \Yii::setAlias('@web', '/');
        \Yii::setAlias('@webroot', __DIR__ . '/../_files/');
    }

    /**
     * Returns View with configured AssetManager.
     *
     * @param array $config may be used to override default AssetManager config
     * @return View
     */
    protected function getView(array $config = [])
    {
        $this->mockApplication();
        $view = new View();
        $view->setAssetManager(new AssetManager($config));
        return $view;
    }

    public function testSimple()
    {
        $view = $this->getView();
        $this->assertEmpty($view->assetBundles);
        TestWebpackBundle::register($view);
        $this->assertCount(1, $view->assetBundles);
        $this->assertArrayHasKey(TestWebpackBundle::class, $view->assetBundles);
        $this->assertInstanceOf(AssetBundle::class, $view->assetBundles[TestWebpackBundle::class]);

        $expected = <<<'EOF'
1<link href="/assets/build/app.e40b1b0b.css" rel="stylesheet">23<script src="/assets/build/runtime.e91b994e.js"></script>
<script src="/assets/build/app.4ff07b2b.js"></script>4
EOF;
        $this->assertEqualsWithoutLE($expected, $view->renderFile(__DIR__ . '/../_files/' . 'rawlayout.php'));
    }

    public function provider()
    {
        return [
            [[]],
            [['entryPointsFile' => 'test']],
            [['entryPoint' => 'app']],
        ];
    }

    /**
     * @dataProvider provider
     */
    public function testBadConfiguration($config)
    {
        $this->expectException(InvalidConfigException::class);
        new TestBadConfigWebpackBundle($config);
    }

    public function testBadManifestFile()
    {
        $view = $this->getView();
        $asset = new TestWebpackBundle(['entryPointsFile' => '@webroot/entrypointsBadJson.txt']);
        $view->getAssetManager()->bundles[TestWebpackBundle::class] = $asset;

        $this->expectException(InvalidArgumentException::class);
        $asset->publish($view->getAssetManager());
    }
}

class TestWebpackBundle extends WebpackAsset
{
    public $entryPointsFile = '@webroot/entrypoints.json';
    public $entryPoint = 'app';
    public $basePath = '@webroot/';
    public $baseUrl = '@web/';
}

class TestBadConfigWebpackBundle extends WebpackAsset
{
}

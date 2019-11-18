<?php

namespace mmo\yii2\assets;

use yii\base\InvalidConfigException;
use yii\helpers\Json;
use yii\web\AssetBundle;

class WebpackAsset extends AssetBundle
{
    /** @var string  */
    public $entryPointsFile;

    /** @var string  */
    public $entryPoint;

    /** @var string  */
    public $basePath;

    /** @var string  */
    public $baseUrl;

    public function init()
    {
        parent::init();
        if (null === $this->entryPointsFile) {
            throw new InvalidConfigException('The "entryPointsFile" property must be set.');
        }

        if (null === $this->entryPoint) {
            throw new InvalidConfigException('The "entryPoint" property must be set.');
        }
    }


    public function publish($am): void
    {
        $path = \Yii::getAlias($this->entryPointsFile);
        if (!is_readable($path)) {
            throw new \InvalidArgumentException(sprintf('Entry point file "%s" is not readable', $path));
        }
        if (!is_file($path)) {
            throw new \InvalidArgumentException(sprintf('Entry point file "%s" is not file', $path));
        }
        $content = file_get_contents($path);
        $entryPoints = Json::decode($content);

        $data = $entryPoints['entrypoints'][$this->entryPoint];
        if (isset($data['js']) && is_array($data['js'])) {
            $this->js = $data['js'];
        }

        if (isset($data['css']) && is_array($data['css'])) {
            $this->css = $data['css'];
        }

        parent::publish($am);
    }
}

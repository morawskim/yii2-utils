<?php

namespace mmo\yii2\tests\actions;

use mmo\yii2\actions\RenderView;
use mmo\yii2\tests\TestCase;
use yii\web\Controller;

class RenderViewTest extends TestCase
{
    public function testViewFile(): void
    {
        $this->mockWebApplication([
            'viewPath' => __DIR__ . '/../_files/views/'
        ]);

        $controller = new Controller('id', \Yii::$app);
        $action = new RenderView(
            'test',
            $controller,
            [
                'viewFile' => '//simple.php',
            ]
        );

        $content = $action->run();
        $this->assertEquals('Hello World!', $content);
    }

    public function testViewFileWithLayout(): void
    {
        $viewPath = __DIR__ . '/../_files/views/';
        $this->mockWebApplication([
            'viewPath' => $viewPath
        ]);
        \Yii::setAlias('@views', $viewPath);

        $controller = new Controller('id', \Yii::$app);
        $action = new RenderView(
            'test',
            $controller,
            [
                'viewFile' => '//simple.php',
                'layout' => '@views/layout.php'
            ]
        );

        $content = $action->run();
        $this->assertEquals('<div>Hello World!</div>', $content);
    }

    public function testViewFileWithParams(): void
    {
        $this->mockWebApplication([
            'viewPath' => __DIR__ . '/../_files/views/'
        ]);

        $controller = new Controller('id', \Yii::$app);
        $action = new RenderView(
            'test',
            $controller,
            [
                'viewFile' => '//params.php',
                'params' => [
                    'foo' => 'foo',
                ]
            ]
        );

        $content = $action->run();
        $this->assertEquals('foo', $content);
    }
}

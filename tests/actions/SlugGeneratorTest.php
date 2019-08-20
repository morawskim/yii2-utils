<?php

namespace mmo\yii2\tests\actions;

use mmo\yii2\actions\SlugGenerator;
use mmo\yii2\tests\TestCase;
use yii\web\Controller;
use yii\web\Request;
use yii\web\Response;

class SlugGeneratorTest extends TestCase
{
    public function testGenearteSlug()
    {
        $text = 'Żółć';
        $slugs = [];
        $this->mockWebApplication();
        \Yii::$app->set('response', $this->mockResponse());

        $controller = new Controller('id', \Yii::$app);
        $action = new SlugGenerator(
            'test',
            $controller,
            [
                'request' => $this->mockRequest($text),
                'findSlugsCallback' => function () use ($slugs) {
                    return $slugs;
                }
            ]
        );

        $response = $action->run();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertIsArray($response->data);
        $this->assertArrayHasKey('slug', $response->data);
        $this->assertEquals('zolc', $response->data['slug']);
    }

    public function testGenearteSlugIfAlreadyExists()
    {
        $text = 'Żółć';
        $slugs = ['zolc', 'zolc-1'];
        $this->mockWebApplication();
        \Yii::$app->set('response', $this->mockResponse());

        $controller = new Controller('id', \Yii::$app);
        $action = new SlugGenerator(
            'test',
            $controller,
            [
                'request' => $this->mockRequest($text),
                'findSlugsCallback' => function () use ($slugs) {
                    return $slugs;
                }
            ]
        );

        $response = $action->run();
        $this->assertEquals('zolc-2', $response->data['slug']);
    }

    public function testGenearteSlugIfEmptyText()
    {
        $text = '';
        $slugs = [];
        $this->mockWebApplication();
        \Yii::$app->set('response', $this->mockResponse());

        $controller = new Controller('id', \Yii::$app);
        $action = new SlugGenerator(
            'test',
            $controller,
            [
                'request' => $this->mockRequest($text),
                'findSlugsCallback' => function () use ($slugs) {
                    return $slugs;
                }
            ]
        );

        $response = $action->run();
        $this->assertEquals('', $response->data['slug']);
    }

    public function testGenearteSlugCustomFieldName()
    {
        $fieldName = 'title';
        $text = 'Żółć';
        $slugs = [];
        $this->mockWebApplication();
        \Yii::$app->set('response', $this->mockResponse());

        $controller = new Controller('id', \Yii::$app);
        $action = new SlugGenerator(
            'test',
            $controller,
            [
                'postFieldName' => $fieldName,
                'request' => $this->mockRequest($text, $fieldName),
                'findSlugsCallback' => function () use ($slugs) {
                    return $slugs;
                }
            ]
        );

        $response = $action->run();
        $this->assertEquals('zolc', $response->data['slug']);
    }

    public function testThrowExceptionIfCallbackNotReturnArray()
    {
        $text = 'Żółć';
        $this->mockWebApplication();
        \Yii::$app->set('response', $this->mockResponse());

        $controller = new Controller('id', \Yii::$app);
        $action = new SlugGenerator(
            'test',
            $controller,
            [
                'request' => $this->mockRequest($text),
                'findSlugsCallback' => function () {
                    return null;
                }
            ]
        );

        $this->expectException(\InvalidArgumentException::class);
        $action->run();
    }

    protected function mockRequest(string $text, string $fieldName = 'text'): Request
    {
        $request = $this->getMockBuilder(Request::class)
            ->onlyMethods(['post'])
            ->getMock();
        $request->expects($this->once())
            ->method('post')
            ->with($fieldName)
            ->willReturn($text);

        /** @var Request $request */
        return $request;
    }

    protected function mockResponse(): Response
    {
        /** @var Response $response */
        $response = $this->getMockBuilder(Response::class)
            ->getMock();
        return $response;
    }
}

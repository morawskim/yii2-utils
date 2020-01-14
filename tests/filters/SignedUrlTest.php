<?php

namespace mmo\yii2\tests\filters;

use mmo\yii2\filters\SignedUrl;
use yii\base\Action;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Request;
use yii\web\UrlManager;
use yii\web\Application;

class SignedUrlTest extends \mmo\yii2\tests\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockWebApplication([
            'components' => [
                'urlManager' => [
                    'class' => UrlManager::class,
                    'baseUrl' => '/base',
                    'scriptUrl' => '/base/index.php',
                    'hostInfo' => 'http://example.com/',
                ],
            ],
        ], Application::class);
    }

    public function providerSignedUrl()
    {
        return [
            'path' => ['/base/index.php?r=page%2Fview&id=10', false],
            'absolute' => ['http://example.com/base/index.php?r=page%2Fview&id=10', true],
        ];
    }

    /**
     * @dataProvider providerSignedUrl
     */
    public function testCheckSignature($toSign, $absolute): void
    {
        $controller = new Controller('id', \Yii::$app);
        $action = new Action('test', $controller);

        $hashKey = 'foo';
        $signature = hash_hmac('sha256', $toSign, $hashKey);
        $url = "$toSign&signature=$signature";
        $filter = new SignedUrl([
            'key' => $hashKey,
            'absolute' => $absolute,
            'request' => $this->mockRequest($url, $signature, $absolute)
        ]);
        $this->assertTrue($filter->beforeAction($action));
    }

    public function testEmptySignatureQueryParameter(): void
    {
        $controller = new Controller('id', \Yii::$app);
        $action = new Action('test', $controller);

        $filter = new SignedUrl([
            'key' => 'foo',
            'absolute' => false,
            'request' => $this->mockRequest('/base/index.php?r=page%2Fview&id=10', null)
        ]);
        $this->expectException(NotFoundHttpException::class);
        $filter->beforeAction($action);
    }

    protected function mockRequest($url, $signature, $absolute = false): Request
    {
        $mock = $this->createMock(Request::class);
        $mock->expects(self::once())
            ->method('getQueryParam')
            ->with($this->equalTo('signature'))
            ->willReturn($signature);

        if ($absolute) {
            $mock->expects(self::once())
                ->method('getAbsoluteUrl')
                ->willReturn($url);
        } else {
            $mock->expects(self::once())
                ->method('getUrl')
                ->willReturn($url);
        }

        return $mock;
    }
}

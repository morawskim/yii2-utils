<?php

namespace mmo\yii2\tests\filters;

use mmo\yii2\filters\IpRequest;
use yii\base\Action;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\Request;

class IpRequestTest extends \mmo\yii2\tests\TestCase
{
    public function testIpIsAllowed(): void
    {
        $allowedIp = '192.168.0.2';
        $controller = new Controller('id', \Yii::$app);
        $action = new Action('test', $controller);
        $filter = new IpRequest([
            'allowedIPs' => [$allowedIp],
            'request' => $this->mockRequest($allowedIp)
        ]);
        $this->assertTrue($filter->beforeAction($action));
    }

    public function testIpIsBlocked(): void
    {
        $controller = new Controller('id', \Yii::$app);
        $action = new Action('test', $controller);
        $filter = new IpRequest([
            'allowedIPs' => ['192.168.0.2'],
            'request' => $this->mockRequest('192.168.15.15')]
        );
        $this->expectException(ForbiddenHttpException::class);
        $filter->beforeAction($action);
    }

    public function testIpRangeIsBlocked(): void
    {
        $controller = new Controller('id', \Yii::$app);
        $action = new Action('test', $controller);
        $filter = new IpRequest([
            'allowedIPs' => ['192.168.1.*'],
            'request' => $this->mockRequest('192.168.15.15')]
        );
        $this->expectException(ForbiddenHttpException::class);
        $filter->beforeAction($action);
    }

    protected function mockRequest(string $userIp): Request
    {
        /** @var Request $request */
        $request = $this->getMockBuilder(Request::class)
            ->onlyMethods(['getUserIP'])
            ->getMock();
        $request->method('getUserIP')->willReturn($userIp);
        return $request;
    }
}

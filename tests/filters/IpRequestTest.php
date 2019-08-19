<?php

namespace mmo\yii2\tests\filters;

use mmo\yii2\filters\IpRequest;
use yii\base\Action;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\Request;

class IpRequestTest extends \mmo\yii2\tests\TestCase
{
    /**
     * @dataProvider dataProviderAllowed
     * @param array $allowedIPs
     * @param string $clientIp
     * @throws ForbiddenHttpException
     */
    public function testIpIsAllowed(array $allowedIPs, string $clientIp): void
    {
        $controller = new Controller('id', \Yii::$app);
        $action = new Action('test', $controller);
        $filter = new IpRequest([
            'allowedIPs' => $allowedIPs,
            'request' => $this->mockRequest($clientIp)
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

    protected function mockRequest(string $userIp): Request
    {
        /** @var Request $request */
        $request = $this->getMockBuilder(Request::class)
            ->onlyMethods(['getUserIP'])
            ->getMock();
        $request->method('getUserIP')->willReturn($userIp);
        return $request;
    }

    public function dataProviderAllowed(): array
    {
        return [
            'simple' => [['192.168.0.2'], '192.168.0.2'],
            'wildcard' => [['192.168.15.*'], '192.168.15.15'],
            'many_ips' => [['192.168.2.*', '192.168.3.3'], '192.168.3.3'],
        ];
    }
}

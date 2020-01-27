<?php

namespace mmo\yii2\tests\actions;

use mmo\yii2\actions\ReportingLogger;
use mmo\yii2\actions\SlugGenerator;
use mmo\yii2\tests\TestCase;
use yii\base\InvalidConfigException;
use yii\log\Logger;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Request;
use yii\web\Response;

class ReportingLoggerTest extends TestCase
{
    public function testReportingDefaultValues(): void
    {
        $bodyParams = ['error' => 'Foo', 'arg1' => 'foo'];
        $this->mockWebApplication();
        \Yii::$app->set('response', new Response(['version' => 1.1]));

        $controller = new Controller('id', \Yii::$app);
        $action = new ReportingLogger(
            'test',
            $controller,
            [
                'request' => $this->mockRequest($bodyParams),
                'logger' => $this->mockLogger($bodyParams, Logger::LEVEL_ERROR, 'application'),
            ]
        );

        $response = $action->run();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals($response->getStatusCode(), 204);
    }

    /**
     * @dataProvider reportingDataProvider
     * @param $bodyParams
     * @param $loggerLevel
     * @param $loggerCategory
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     */
    public function testReporting($bodyParams, $loggerLevel, $loggerCategory): void
    {
        $this->mockWebApplication();
        \Yii::$app->set('response', new Response(['version' => 1.1]));

        $controller = new Controller('id', \Yii::$app);
        $action = new ReportingLogger(
            'test',
            $controller,
            [
                'logCategory' => $loggerCategory,
                'logLevel' => $loggerLevel,
                'request' => $this->mockRequest($bodyParams),
                'logger' => $this->mockLogger($bodyParams, $loggerLevel, $loggerCategory),
            ]
        );

        $response = $action->run();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals($response->getStatusCode(), 204);
    }

    public function testNullLogLevel(): void
    {
        $this->mockWebApplication();
        \Yii::$app->set('response', new Response(['version' => 1.1]));

        $controller = new Controller('id', \Yii::$app);

        $this->expectException(InvalidConfigException::class);
        new ReportingLogger(
            'test',
            $controller,
            [
                'logLevel' => null,
            ]
        );
    }

    public function testEmptyBodyParams(): void
    {
        $this->mockWebApplication();
        \Yii::$app->set('response', new Response(['version' => 1.1]));

        $controller = new Controller('id', \Yii::$app);

        $this->expectException(BadRequestHttpException::class);
        $action = new ReportingLogger(
            'test',
            $controller,
            [
                'request' => $this->mockRequest([]),
            ]
        );
        $action->run();
    }

    /**
     * @dataProvider
     */
    public function reportingDataProvider(): array
    {
        return [
            [['error' => 'Foo', 'arg1' => 'foo'], Logger::LEVEL_INFO, null],
            [['error' => 'Foo', 'arg1' => 'foo'], Logger::LEVEL_WARNING, 'application.js'],
        ];
    }

    protected function mockRequest(array $bodyParams): Request
    {
        $request = $this->getMockBuilder(Request::class)
            ->onlyMethods(['getBodyParams'])
            ->getMock();
        $request->expects($this->once())
            ->method('getBodyParams')
            ->willReturn($bodyParams);

        /** @var Request $request */
        return $request;
    }

    protected function mockLogger($message, $level, $category)
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->onlyMethods(['log'])
            ->getMock();
        $logger->expects($this->once())
            ->method('log')
            ->with(static::equalTo($message), static::equalTo($level), static::equalTo($category));

        /** @var Logger $logger */
        return $logger;
    }
}

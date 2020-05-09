<?php

namespace mmo\yii2\tests\actions;

use Laminas\Diagnostics\Check\Callback;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\Skip;
use Laminas\Diagnostics\Result\Success;
use Laminas\Diagnostics\Result\Warning;
use Laminas\Diagnostics\Runner\Runner;
use mmo\yii2\actions\LaminasDiagnostics;
use yii\base\InvalidConfigException;
use yii\web\Controller;
use yii\web\Response;

class LaminasDiagnosticsTest extends \mmo\yii2\tests\TestCase
{
    public function testWithoutRunner(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('runner');

        $this->mockWebApplication();
        $controller = new Controller('id', \Yii::$app);
        new LaminasDiagnostics('test', $controller, []);
    }

    public function testWithNotExistingCollectorRegistryComponent(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('configuration');

        $this->mockWebApplication();
        $controller = new Controller('id', \Yii::$app);
        new LaminasDiagnostics('test', $controller, ['runner' => 'runner']);
    }

    public function testWithPassNoCollectorRegistryInstance(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('configuration');

        $this->mockWebApplication();
        $controller = new Controller('id', \Yii::$app);
        new LaminasDiagnostics('test', $controller, ['runner' => new \stdClass()]);
    }

    public function testLaminasDiagnosticsAction(): void
    {
        $this->mockWebApplication();
        $controller = new Controller('id', \Yii::$app);

        $runner = new Runner();
        $runner->addCheck(new Callback(function () {
            return new Success('Success');
        }));
        $runner->addCheck(new Callback(function () {
            return new Failure('Failure');
        }));
        $runner->addCheck(new Callback(function () {
            return new Skip('Skip');
        }));
        $runner->addCheck(new Callback(function () {
            return new Warning('Warning');
        }));

        $action = new LaminasDiagnostics('test', $controller, [
            'runner' => $runner
        ]);

        $response = $action->run();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertStringContainsString('application/json', $response->getHeaders()->get('Content-Type'));
        $this->assertEquals('no-cache', $response->getHeaders()->get('Pragma'));
        $this->assertEquals('no-cache', $response->getHeaders()->get('Cache-Control'));
        $this->assertEquals('0', $response->getHeaders()->get('Expires'));
        $this->assertNotEmpty($response->content);
        $this->assertJson($response->content);
        $this->assertJsonStringEqualsJsonFile(
            __DIR__ . '/../_files/actions/laminasDiagnosticsResponse.json',
            $response->content
        );
    }

    /**
     * @dataProvider providerForTestPassed
     * @param Runner $runner
     * @param bool $expect
     */
    public function testPassedKey(Runner $runner, bool $expect): void
    {
        $this->mockWebApplication();
        $controller = new Controller('id', \Yii::$app);

        $action = new LaminasDiagnostics('test', $controller, [
            'runner' => $runner
        ]);

        $response = $action->run();
        $this->assertJson($response->content);

        $data = \json_decode($response->content, true);
        $this->assertArrayHasKey('passed', $data);
        $this->assertEquals($expect, $data['passed']);
    }

    public function providerForTestPassed(): array
    {
        $runnerOnlySuccess = new Runner();
        $runnerOnlySuccess->addCheck(new Callback(function () {
            return new Success('Success');
        }));

        $runner = new Runner();
        $runner->addCheck(new Callback(function () {
            return new Success('Success');
        }));
        $runner->addCheck(new Callback(function () {
            return new Failure('Fail');
        }));

        return [
            [$runnerOnlySuccess, true],
            [$runner, false],
        ];
    }
}

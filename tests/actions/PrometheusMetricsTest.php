<?php

namespace mmo\yii2\tests\actions;

use mmo\yii2\actions\PrometheusMetrics;
use mmo\yii2\tests\TestCase;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\InMemory;
use yii\web\Controller;
use yii\web\Response;

class PrometheusMetricsTest extends TestCase
{
    public function testRenderPrometheusMetrics(): void
    {
        $collectionRegistry = new CollectorRegistry(new InMemory());
        $counter = $collectionRegistry->registerCounter('test', 'some_counter', 'it increases', ['type']);
        $counter->incBy(3, ['blue']);

        $this->mockWebApplication();
        $controller = new Controller('id', \Yii::$app);
        $action = new PrometheusMetrics(
            'test',
            $controller,
            [
                'collectorRegistry' => $collectionRegistry
            ]
        );

        $response = $action->run();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertStringContainsString('text/plain', $response->getHeaders()->get('Content-Type'));
        $this->assertNotEmpty($response->content);
        $this->assertStringEqualsFile(
            __DIR__ . '/../_files/actions/prometheusMetricsResponse.txt',
            $response->content
        );
    }
}

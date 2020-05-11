<?php

namespace mmo\yii2\tests\services;

use mmo\yii2\filters\PrometheusWebMetrics;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\InMemory;
use yii\base\InvalidConfigException;

class PrometheusWebMetricsTest extends \mmo\yii2\tests\TestCase
{
    public function testNoNamespace(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('namespace');

        new PrometheusWebMetrics([
            'registerShutdownFunction' => false,
            'collectorRegistry' => new CollectorRegistry(new InMemory()),
        ]);
    }

    public function testWithoutCollectorRegistry(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('collectorRegistry');

        new PrometheusWebMetrics([
            'registerShutdownFunction' => false,
            'namespace' => 'example',
        ]);
    }

    public function testWithNotExistingCollectorRegistryComponent(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('configuration');

        new PrometheusWebMetrics([
            'registerShutdownFunction' => false,
            'namespace' => 'example',
            'collectorRegistry' => 'collectorRegistry',
        ]);
    }

    public function testWithPassNoCollectorRegistryInstance(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('configuration');

        new PrometheusWebMetrics([
            'registerShutdownFunction' => false,
            'namespace' => 'example',
            'collectorRegistry' => new \stdClass(),
        ]);
    }

    public function testCollectMethods(): void
    {
        $collectorRegistry = new CollectorRegistry(new InMemory());
        $namespace = 'example';
        $behavior = new PrometheusWebMetrics([
            'registerShutdownFunction' => false,
            'collectorRegistry' => $collectorRegistry,
            'namespace' => $namespace
        ]);

        $this->assertCount(0, $collectorRegistry->getMetricFamilySamples());

        $this->mockWebApplication(['components' => ['request' => ['url' => '/example-page?foo=bar']]]);
        $behavior->collectRequestTimeMetric();
        $behavior->collectMemoryPeakMetric();

        $collectorRegistry->getHistogram($namespace, 'response_time_seconds');
        $this->assertCount(2, $collectorRegistry->getMetricFamilySamples());

        $string = implode('', $collectorRegistry->getMetricFamilySamples()[0]->getSamples()[0]->getLabelValues());
        $this->assertStringNotContainsString('foo', $string);
    }
}

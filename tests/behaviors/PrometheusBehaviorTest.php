<?php

namespace mmo\yii2\tests\behaviors;

use mmo\yii2\behaviors\PrometheusBehavior;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\InMemory;
use yii\base\Event;
use yii\base\InvalidConfigException;
use yii\web\Application;
use yii\web\Response;

class PrometheusBehaviorTest extends \mmo\yii2\tests\TestCase
{
    public function testNoNamespace(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('namespace');

        new PrometheusBehavior([
            'collectorRegistry' => new CollectorRegistry(new InMemory()),
        ]);
    }

    public function testWithoutCollectorRegistry(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('collectorRegistry');

        new PrometheusBehavior([
            'namespace' => 'example',
        ]);
    }

    public function testWithNotExistingCollectorRegistryComponent(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('configuration');

        new PrometheusBehavior([
            'namespace' => 'example',
            'collectorRegistry' => 'collectorRegistry',
        ]);
    }

    public function testWithPassNoCollectorRegistryInstance(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('configuration');

        new PrometheusBehavior([
            'namespace' => 'example',
            'collectorRegistry' => new \stdClass(),
        ]);
    }

    public function testAttachAndDetachToEvents(): void
    {
        $behavior = new PrometheusBehavior([
            'collectorRegistry' => new CollectorRegistry(new InMemory()),
            'namespace' => 'example'
        ]);

        $this->mockWebApplication();
        $this->assertFalse(\Yii::$app->hasEventHandlers(Application::EVENT_BEFORE_REQUEST));
        $this->assertFalse(\Yii::$app->getResponse()->hasEventHandlers(Response::EVENT_BEFORE_SEND));
        $behavior->attach(\Yii::$app);

        $this->assertTrue(\Yii::$app->hasEventHandlers(Application::EVENT_BEFORE_REQUEST));
        $this->assertTrue(\Yii::$app->getResponse()->hasEventHandlers(Response::EVENT_BEFORE_SEND));

        $behavior->detach();
        $this->assertFalse(\Yii::$app->hasEventHandlers(Application::EVENT_BEFORE_REQUEST));
        $this->assertFalse(\Yii::$app->getResponse()->hasEventHandlers(Response::EVENT_BEFORE_SEND));
    }

    public function testAbc(): void
    {
        $collectorRegistry = new CollectorRegistry(new InMemory());
        $namespace = 'example';
        $behavior = new PrometheusBehavior([
            'collectorRegistry' => $collectorRegistry,
            'namespace' => $namespace
        ]);

        $this->assertCount(0, $collectorRegistry->getMetricFamilySamples());

        $this->mockWebApplication(['components' => ['request' => ['url' => '/example-page']]]);
        $behavior->attach(\Yii::$app);
        \Yii::$app->trigger(Application::EVENT_BEFORE_REQUEST, new Event());
        \Yii::$app->getResponse()->trigger(Response::EVENT_BEFORE_SEND, new Event());

        $collectorRegistry->getHistogram($namespace, 'response_time_seconds');
        $this->assertCount(1, $collectorRegistry->getMetricFamilySamples());
    }
}
<?php

namespace mmo\yii2\tests\behaviors;

use mmo\yii2\behaviors\PrometheusBehavior;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\InMemory;
use yii\base\Action;
use yii\base\Controller;
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

    public function testTriggers(): void
    {
        $collectorRegistry = new CollectorRegistry(new InMemory());
        $namespace = 'example';
        $behavior = new PrometheusBehavior([
            'collectorRegistry' => $collectorRegistry,
            'namespace' => $namespace
        ]);

        $this->assertCount(0, $collectorRegistry->getMetricFamilySamples());

        $this->mockWebApplication([
            'components' => ['request' => ['url' => '/example-page?foo=bar']],
            'requestedAction' => new Action('foo-bar', $this->createMock(Controller::class))
        ]);
        $behavior->attach(\Yii::$app);
        \Yii::$app->trigger(Application::EVENT_BEFORE_REQUEST, new Event());
        \Yii::$app->getResponse()->trigger(Response::EVENT_BEFORE_SEND, new Event());

        $collectorRegistry->getHistogram($namespace, 'response_time_seconds');
        $this->assertCount(2, $collectorRegistry->getMetricFamilySamples());

        $string = implode('', $collectorRegistry->getMetricFamilySamples()[0]->getSamples()[0]->getLabelValues());
        $this->assertStringNotContainsString('foo', $string);

        //

        $this->assertSame('example_requests_total', $collectorRegistry->getMetricFamilySamples()[0]->getName());
        $this->assertCount(1, $collectorRegistry->getMetricFamilySamples()[0]->getSamples());
        $this->assertEquals(1, $collectorRegistry->getMetricFamilySamples()[0]->getSamples()[0]->getValue());

        $this->assertSame('example_response_time_seconds', $collectorRegistry->getMetricFamilySamples()[1]->getName());
        $this->assertGreaterThan(1, $collectorRegistry->getMetricFamilySamples()[1]->getSamples());
        $this->assertSame('/foo-bar', $collectorRegistry->getMetricFamilySamples()[1]->getSamples()[0]->getLabelValues()[1]);
    }
}

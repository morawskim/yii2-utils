<?php

namespace mmo\yii2\filters;

use Prometheus\CollectorRegistry;
use Prometheus\Histogram;
use yii\base\ActionFilter;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\web\Request;
use yii\web\Response;

class PrometheusWebMetrics extends ActionFilter
{
    /** @var CollectorRegistry|array|string */
    public $collectorRegistry;

    /** @var string */
    public $namespace;

    /**
     * Set to false, if you don't want to register PHP shutdown function to collect metrics.
     *
     * Used in unit tests.
     * @var bool
     */
    public $registerShutdownFunction = true;

    /**
     * @var Request the current request. If not set, the `request` application component will be used.
     */
    public $request;

    /**
     * @var Response the response to be sent. If not set, the `response` application component will be used.
     */
    public $response;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init(): void
    {
        if (empty($this->namespace)) {
            throw new InvalidConfigException('The "namespace" property must be set.');
        }
        try {
            $this->collectorRegistry = Instance::ensure($this->collectorRegistry, CollectorRegistry::class);
        } catch (InvalidConfigException $e) {
            throw new InvalidConfigException('The "collectorRegistry" property has wrong configuration.', 0, $e);
        }
        if (!$this->collectorRegistry instanceof CollectorRegistry) {
            throw new InvalidConfigException(
                sprintf('The "collectorRegistry" property must be instance of "%s"', CollectorRegistry::class)
            );
        }
    }

    public function beforeAction($action): bool
    {
        if ($this->registerShutdownFunction) {
            register_shutdown_function([$this, 'collectRequestTimeMetric']);
            register_shutdown_function([$this, 'collectMemoryPeakMetric']);
        }
        return true;
    }

    public function collectRequestTimeMetric(): void
    {
        $histogram = $this->collectorRegistry->getOrRegisterHistogram(
            $this->namespace,
            'response_time_seconds',
            'It observes response time.',
            [
                'method',
                'path',
                'status_code',
            ]
        );

        /** @var  Histogram $histogram */
        $histogram->observe(
            microtime(true) - YII_BEGIN_TIME,
            [
                $this->getRequest()->method,
                $this->getRequest()->pathInfo,
                $this->getResponse()->getStatusCode(),
            ]
        );
    }

    public function collectMemoryPeakMetric(): void
    {
        $histogram = $this->collectorRegistry->getOrRegisterHistogram(
            $this->namespace,
            'response_memory_peak',
            'It observes memory peak usage.',
            [
                'method',
                'path',
                'status_code',
            ]
        );

        /** @var  Histogram $histogram */
        $histogram->observe(
            memory_get_peak_usage(),
            [
                $this->getRequest()->method,
                $this->getRequest()->pathInfo,
                $this->getResponse()->getStatusCode(),
            ]
        );
    }

    private function getRequest(): Request
    {
        if (null === $this->request) {
            $this->request = \Yii::$app->getRequest();
        }

        if (!$this->request instanceof Request) {
            $type = is_object($this->request) ? get_class($this->request) : gettype($this->request);
            throw new InvalidArgumentException(
                sprintf('Expect \yii\web\Request get "%s"', $type)
            );
        }

        return $this->request;
    }

    private function getResponse(): Response
    {
        if (null === $this->response) {
            $this->response = \Yii::$app->getResponse();
        }

        return $this->response;
    }
}

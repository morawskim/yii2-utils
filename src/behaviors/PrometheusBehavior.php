<?php

namespace mmo\yii2\behaviors;

use Prometheus\CollectorRegistry;
use Prometheus\Histogram;
use yii\base\Behavior;
use yii\base\Event;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\web\Application;
use yii\web\Request;
use yii\web\Response;

class PrometheusBehavior extends Behavior
{
    /** @var CollectorRegistry|array|string */
    public $collectorRegistry;

    /** @var string */
    public $namespace;

    /**
     * @var Request the current request. If not set, the `request` application component will be used.
     */
    public $request;

    /**
     * @var Response the response to be sent. If not set, the `response` application component will be used.
     */
    public $response;

    /** @var float */
    private $start;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init(): void
    {
        parent::init();
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

    public function events(): array
    {
        return [
            Application::EVENT_BEFORE_REQUEST => [$this, 'beforeAction']
        ];
    }


    /**
     * {@inheritdoc}
     */
    public function attach($owner)
    {
        parent::attach($owner);

        /** @var Application $owner */
        $owner = $this->owner;
        $owner->getResponse()->on(Response::EVENT_BEFORE_SEND, [$this, 'afterAction']);
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        if ($this->owner) {
            /** @var Application $owner */
            $owner = $this->owner;
            $owner->getResponse()->off(Response::EVENT_BEFORE_SEND, [$this, 'afterAction']);
        }
        parent::detach();
    }


    public function beforeAction(Event $event): void
    {
        $this->start = microtime(true);
        $this->collectorRegistry->getOrRegisterCounter(
            $this->namespace,
            'requests_total',
            'Counter of request'
        )->inc();
    }

    public function afterAction(Event $event): void
    {
        $histogram = $this->collectorRegistry->getOrRegisterHistogram(
            $this->namespace,
            'response_time_seconds',
            'It observes response time.',
            [
                'method',
                'actionUniqueId',
                'status_code',
            ]
        );

        if ($this->owner instanceof Application) {
            $actionUniqueId = $this->owner->requestedAction->uniqueId;
        } else {
            $actionUniqueId = 'unknow';
        }

        /** @var  Histogram $histogram */
        $histogram->observe(
            microtime(true) - $this->start,
            [
                $this->getRequest()->method,
                $actionUniqueId,
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

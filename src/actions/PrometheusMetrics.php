<?php

namespace mmo\yii2\actions;

use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;
use yii\base\Action;
use yii\web\Response;

class PrometheusMetrics extends Action
{
    /** @var Response */
    public $response;

    /** @var CollectorRegistry */
    public $collectorRegistry;

    public function run(): Response
    {
        $renderer = new RenderTextFormat();
        $result = $renderer->render($this->collectorRegistry->getMetricFamilySamples());

        $response = $this->getResponse();
        $response->format = Response::FORMAT_RAW;
        $response->headers->set('Content-Type', RenderTextFormat::MIME_TYPE);
        $response->content = $result;
        return $response;
    }

    protected function getResponse(): Response
    {
        if (null === $this->response) {
            $this->response = \Yii::$app->getResponse();
        }
        return $this->response;
    }
}

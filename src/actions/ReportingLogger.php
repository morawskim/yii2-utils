<?php

namespace mmo\yii2\actions;

use yii\base\Action;
use yii\base\InvalidConfigException;
use yii\log\Logger;
use yii\web\BadRequestHttpException;
use yii\web\Request;
use yii\web\Response;

class ReportingLogger extends Action
{
    /**
     * the level of the message for Logger
     * @see Logger
     * @var int
     */
    public $logLevel = Logger::LEVEL_ERROR;

    /**
     *  the category of the message for Logger
     * @see Logger
     * @var string
     */
    public $logCategory = 'application';

    /** @var Logger */
    public $logger;

    /** @var Request */
    public $request;

    /** @var Response */
    public $response;

    public function init()
    {
        if (null === $this->logLevel) {
            throw new InvalidConfigException('The "logLevel" property must be set.');
        }
    }

    /**
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function run(): Response
    {
        $data = $this->getRequest()->getBodyParams();

        if (empty($data)) {
            throw new BadRequestHttpException();
        }

        $this->getLogger()->log(
            $data,
            $this->logLevel,
            $this->logCategory
        );

        return $this->getResponse()->setStatusCode(204);
    }

    protected function getLogger(): Logger
    {
        if (null === $this->logger) {
            $this->logger = \Yii::getLogger();
        }
        return $this->logger;
    }

    protected function getRequest(): Request
    {
        if (null === $this->request) {
            $this->request = \Yii::$app->getRequest();
        }
        return $this->request;
    }

    protected function getResponse(): Response
    {
        if (null === $this->response) {
            $this->response = \Yii::$app->getResponse();
        }
        return $this->response;
    }
}

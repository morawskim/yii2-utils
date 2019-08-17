<?php

namespace mmo\yii2\actions;

use yii\base\Action;
use yii\helpers\ArrayHelper;
use yii\web\Request;
use yii\web\Response;

class Select2Autocomplete extends Action
{
    /** @var string */
    public $requestFieldName = 'term';

    /** @var callable */
    public $entriesCallback;

    /** @var Request */
    public $request;

    /** @var Response */
    public $response;

    /** @var string|callable */
    public $select2Key = 'id';

    /** @var string|callable */
    public $select2Text = 'label';

    public function run()
    {
        $term = $this->getRequest()->get($this->requestFieldName, '');
        $entries = \call_user_func($this->entriesCallback, $term);
        $data = ['results' => $this->rowsToItems($entries)];

        return $this->asJson($data);
    }

    public function rowsToItems(array $array)
    {
        $data = [];
        foreach ($array as $item) {
            $data[] = [
                'id' => ArrayHelper::getValue($item, $this->select2Key),
                'text' => ArrayHelper::getValue($item, $this->select2Text),
            ];
        }

        return $data;
    }

    /**
     * Send data formatted as JSON.
     *
     * This method is a shortcut for sending data formatted as JSON. It will return
     * the [[Application::getResponse()|response]] application component after configuring
     * the [[Response::$format|format]] and setting the [[Response::$data|data]] that should
     * be formatted. A common usage will be:
     *
     * ```php
     * return $this->asJson($data);
     * ```
     *
     * @param mixed $data the data that should be formatted.
     * @return Response a response that is configured to send `$data` formatted as JSON.
     * @since 2.0.11
     * @see Response::$format
     * @see Response::FORMAT_JSON
     * @see JsonResponseFormatter
     */
    protected function asJson($data): Response
    {
        $response = $this->getResponse();
        $response->format = Response::FORMAT_JSON;
        $response->data = $data;
        return $response;
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

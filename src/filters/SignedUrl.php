<?php

namespace mmo\yii2\filters;

use GuzzleHttp\Psr7\Uri;
use yii\base\ActionFilter;
use yii\base\InvalidConfigException;
use yii\web\NotFoundHttpException;
use yii\web\Request;

class SignedUrl extends ActionFilter
{
    /**
     * @var Request the current request. If not set, the `request` application component will be used.
     */
    public $request;

    /** @var string */
    public $key;

    /** @var bool */
    public $absolute;

    /** @var string the message to be displayed when request isn't allowed */
    public $errorMessage = 'Wrong URL signed parameter';

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        if (null === $this->key) {
            throw new InvalidConfigException('The "key" property must be set.');
        }

        if ($this->request === null) {
            $this->request = \Yii::$app->getRequest();
        }
    }

    /**
     * {@inheritdoc}
     * @throws NotFoundHttpException
     */
    public function beforeAction($action): bool
    {
        if ($this->checkSignature()) {
            return true;
        }

        throw new NotFoundHttpException($this->errorMessage);
    }

    protected function checkSignature(): bool
    {
        $url = $this->absolute ? $this->request->getAbsoluteUrl() : $this->request->getUrl();
        $urlParts = parse_url($url);
        $queryParams = [];
        parse_str($urlParts['query'] ?? '', $queryParams);
        unset($queryParams['signature']);

        $uri = new Uri($url);
        $uri = $uri->withQuery(http_build_query($queryParams));
        $original = (string) $uri;

        $signature = hash_hmac('sha256', $original, $this->key);
        return hash_equals($signature, (string) $this->request->getQueryParam('signature', ''));
    }
}

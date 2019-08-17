<?php

namespace mmo\yii2\actions;

use yii\base\Action;
use yii\helpers\Inflector;
use yii\web\Request;
use yii\web\Response;

class SlugGenerator extends Action
{
    /** @var callable */
    public $findSlugsCallback;

    /** @var string */
    public $postFieldName = 'text';

    /** @var Request */
    public $request;

    /** @var Response */
    public $response;

    /**
     * @return Response
     */
    public function run(): Response
    {
        $text = $this->getRequest()->post($this->postFieldName, null);
        if ('' === $text) {
            return $this->controller->asJson(['slug' => '']);
        }

        $slug = Inflector::slug($text);
        $slugs = \call_user_func($this->findSlugsCallback, $slug);
        if (!\is_array($slugs)) {
            $type = \is_object($slugs) ? \get_class($slugs) : \gettype($slugs);
            throw new \InvalidArgumentException(sprintf('Expect array. Get "%s"', $type));
        }

        if (\count($slugs) > 0 && \in_array($slug, $slugs, true)) {
            $max = 0;
            do {
                $str = $slug . '-' . ++$max;
                if (!\in_array($str, $slugs, true)) {
                    break;
                }
            } while(true);

            $slug .= '-' . $max;
        }

        return $this->controller->asJson(['slug' => $slug]);
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

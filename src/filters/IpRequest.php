<?php

namespace mmo\yii2\filters;

use yii\base\ActionFilter;
use yii\web\ForbiddenHttpException;
use yii\web\Request;

class IpRequest extends ActionFilter
{
    /**
     * @var Request the current request. If not set, the `request` application component will be used.
     */
    public $request;

    /**
     * @var array the list of IPs that are allowed to access action.
     * Each array element represents a single IP filter which can be either an IP address
     * or an address with wildcard (e.g. 192.168.0.*) to represent a network segment.
     * The default value is `['*']`, which means the action can be accessed
     * by everyone.
     */
    public $allowedIPs = ['*'];

    /** @var string the message to be displayed when request isn't allowed */
    public $errorMessage = 'Access to action is denied due to IP address restriction.';

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        if ($this->request === null) {
            $this->request = \Yii::$app->getRequest();
        }
    }

    /**
     * {@inheritdoc}
     * @throws ForbiddenHttpException
     */
    public function beforeAction($action): bool
    {
        if ($this->checkAccess()) {
            return true;
        }

        throw new ForbiddenHttpException($this->errorMessage);
    }

    protected function checkAccess(): bool
    {
        $allowed = false;
        $ip = $this->request->getUserIP();
        foreach ($this->allowedIPs as $filter) {
            if ($filter === '*' || $filter === $ip || (($pos = strpos($filter, '*')) !== false && !strncmp($ip, $filter, $pos))) {
                $allowed = true;
                break;
            }
        }

        return $allowed;
    }
}

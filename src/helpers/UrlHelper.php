<?php

namespace mmo\yii2\helpers;

use yii\base\InvalidArgumentException;
use yii\helpers\BaseUrl;

class UrlHelper extends BaseUrl
{
    public static function toRouteSigned($route, string $key, $scheme = false)
    {
        $route = (array) $route;
        if (array_key_exists('signature', $route)) {
            throw new InvalidArgumentException(
                '"Signature" is a reserved parameter when generating signed routes. Please rename your route parameter.'
            );
        }

        $route['signature'] = hash_hmac('sha256', self::toRoute($route, $scheme), $key);
        return self::toRoute($route, $scheme);
    }
}

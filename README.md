[![Build Status](https://travis-ci.com/morawskim/yii2-utils.svg?branch=master)](https://travis-ci.com/morawskim/yii2-utils)
[![codecov](https://codecov.io/gh/morawskim/yii2-utils/branch/master/graph/badge.svg)](https://codecov.io/gh/morawskim/yii2-utils)

## Action ReportingLogger
If you don't use JSON or another format you can skip this step.
To use this action you first need to configure `request` component.
In configuration file find configuration for `request` component and set additional parsers for body request.
```
'parsers' => [
    'application/json' => 'yii\web\JsonParser',
]
```

Next in your controller (web), you need to disable verification of `csrf` token.
You can do this by overwrite variable `enableCsrfValidation` as follow `public $enableCsrfValidation = false;`.
You also need method `actions` in controller class.
```php
/**
 * {@inheritdoc}
 */
public function actions()
{
    return [
        'js' => [
            'class' => \mmo\yii2\actions\ReportingLogger::class,
            'logCategory' => 'application.js'
        ]
    ];
}
```
Now if you call for example `curl -XPOST -H'Content-Type: application/json'  -d'{"foo": "bar"}' -v 'http://<server>/index.php?r=<controller/js>'` the body parameters will be logged to file `app.log` with category set to `application.js`.

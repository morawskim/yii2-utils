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

## Action PrometheusMetrics

This action render metrics for scraping by prometheus.
You should define service `\Prometheus\CollectorRegistry` in container.
```php
/** @var $container \yii\di\Container */
$container->setSingleton(\Prometheus\CollectorRegistry::class, function ($container, $params, $config) {
    return new \Prometheus\CollectorRegistry(new \Prometheus\Storage\Redis([
        'host' => '127.0.0.1',
        'port' => 6379,
        'timeout' => 0.3,
        'read_timeout' => 5,
        'persistent_connections' => false,
        'password' => null,
    ]));
}); 
``` 
You can define container service in configuration file.
```php
return [
    'container' => [
        'singletons' => [
            \Prometheus\CollectorRegistry::class => function ($container, $params, $config) {
                //definition
            },
        ]
    ],
    // ...
];
```
You need method `actions` in controller class.
```php
/**
 * {@inheritdoc}
 */
public function actions()
{
    return [
        'metrics' => [
            'class' => \mmo\yii2\actions\PrometheusMetrics::class,
            'collectorRegistry' => Yii::$container->get(\Prometheus\CollectorRegistry::class)
        ]
    ];
}
```

<?php

namespace mmo\yii2\tests\helpers;

use mmo\yii2\helpers\UrlHelper;
use yii\web\Controller;
use yii\base\Action;
use yii\base\Module;

class UrlHelperTest extends \mmo\yii2\tests\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockWebApplication([
            'components' => [
                'request' => [
                    'class' => 'yii\web\Request',
                    'cookieValidationKey' => '123',
                    'scriptUrl' => '/base/index.php',
                    'hostInfo' => 'http://example.com/',
                    'url' => '/base/index.php&r=site%2Fcurrent&id=42',
                ],
                'urlManager' => [
                    'class' => 'yii\web\UrlManager',
                    'baseUrl' => '/base',
                    'scriptUrl' => '/base/index.php',
                    'hostInfo' => 'http://example.com/',
                ],
//                'user' => [
//                    'identityClass' => UserIdentity::className(),
//                ],
            ],
        ], '\yii\web\Application');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * Mocks controller action with parameters.
     *
     * @param string $controllerId
     * @param string $actionID
     * @param string $moduleID
     * @param array  $params
     */
    protected function mockAction($controllerId, $actionID, $moduleID = null, $params = [])
    {
        \Yii::$app->controller = $controller = new Controller($controllerId, \Yii::$app);
        $controller->actionParams = $params;
        $controller->action = new Action($actionID, $controller);
        if ($moduleID !== null) {
            $controller->module = new Module($moduleID);
        }
    }

    /**
     * @dataProvider dataProviderSignRouteWithoutSchema
     * @param mixed $route
     * @param bool $schema
     * @param string $expectUrl
     */
    public function testToRouteSigned($route, bool $schema, string $expectUrl)
    {
        $key = 'secretkey';
        $this->mockAction('page', 'view', null, ['id' => 10]);
        $url = UrlHelper::toRouteSigned($route, $key, $schema);

        $queryParams = [];
        $queryString = parse_url($url, PHP_URL_QUERY);
        parse_str($queryString, $queryParams);
        $hash = hash_hmac('sha256', UrlHelper::toRoute($route, $schema), $key);

        $this->assertArrayHasKey('signature', $queryParams);
        $this->assertEquals($queryParams['signature'], $hash);
        $this->assertEquals($expectUrl, $url);
    }

    public function dataProviderSignRouteWithoutSchema(): array
    {
        return [
            'route_string' => ['/', false, '/base/index.php?r=&signature=f4b4b3a75ef211a54a04c3cf5aac345bdb190359ed3746b384c47b83a8ad0b58'],
            'route_array' => [['/'], false, '/base/index.php?r=&signature=f4b4b3a75ef211a54a04c3cf5aac345bdb190359ed3746b384c47b83a8ad0b58'],
            'route_with_param' => [['/page/view', 'id' => 10], false, '/base/index.php?r=page%2Fview&id=10&signature=27860adc86c0707fdf5aa008090e767085ff34e73152831af2835b1342842029'],

            'route_string_absolute' => ['/', true, 'http://example.com/base/index.php?r=&signature=7799e5280899b005346ac8d10289277cd3ffd840eb508b292b55d7576a0dec4b'],
            'route_array_absolute' => [['/'], true, 'http://example.com/base/index.php?r=&signature=7799e5280899b005346ac8d10289277cd3ffd840eb508b292b55d7576a0dec4b'],
            'route_with_absolute' => [['/page/view', 'id' => 10], true, 'http://example.com/base/index.php?r=page%2Fview&id=10&signature=e9713812901ad8c182e56d33ef94496aad8d444913ef7c07c47ff4419e08fff4'],
        ];
    }
}

<?php

namespace mmo\yii2\tests\actions;

use mmo\yii2\actions\Select2Autocomplete;
use mmo\yii2\tests\TestCase;
use yii\web\Controller;
use yii\web\Request;
use yii\web\Response;

class Select2AutocompleteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockWebApplication();
    }

    public function testRunDefaultSelect2Attributes(): void
    {
        $term = 'asd';
        $entries = [['id' => 1, 'label' => 'asd1'], ['id' => 2, 'label' => 'asd2']];

        $controller = new Controller('id', \Yii::$app);
        $action = new Select2Autocomplete(
            'test',
            $controller,
            [
                'request' => $this->mockRequest($term),
                'response' => $this->mockResponse(),
                'entriesCallback' => function () use ($entries) {
                    return $entries;
                }
            ]
        );

        $response = $action->run();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertIsArray($response->data);
        $this->assertArrayHasKey('results', $response->data);

        $expectedArray = [
            ['id' => 1, 'text' => 'asd1'],
            ['id' => 2, 'text' => 'asd2']
        ];
        $this->assertEquals($expectedArray, $response->data['results']);
    }

    public function testRunSelect2AttributesAsCallbacks(): void
    {
        $term = 'asd';

        $model1 = (object)[
            'id' => 1,
            'firstName' => 'Marcin',
            'lastName' => 'Nowak',
        ];

        $model2 = (object)[
            'id' => 2,
            'firstName' => 'Jan',
            'lastName' => 'Kowalski',
        ];
        $entries = [$model1, $model2];

        $controller = new Controller('id', \Yii::$app);
        $action = new Select2Autocomplete(
            'test',
            $controller,
            [
                'select2Text' => function (\stdClass $model) {
                    return sprintf('%s %s', $model->firstName, $model->lastName);
                },
                'select2Key' => function (\stdClass $model) {
                    return $model->id;
                },
                'request' => $this->mockRequest($term),
                'response' => $this->mockResponse(),
                'entriesCallback' => function () use ($entries) {
                    return $entries;
                }
            ]
        );

        $response = $action->run();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertIsArray($response->data);
        $this->assertArrayHasKey('results', $response->data);

        $expectedArray = [
            ['id' => 1, 'text' => 'Marcin Nowak'],
            ['id' => 2, 'text' => 'Jan Kowalski']
        ];
        $this->assertEquals($expectedArray, $response->data['results']);
    }

    public function testRunSelect2AttributesAsFieldString(): void
    {
        $term = 'asd';

        $model1 = (object)[
            'id' => 1,
            'firstName' => 'Marcin',
            'lastName' => 'Nowak',
        ];

        $model2 = (object)[
            'id' => 2,
            'firstName' => 'Jan',
            'lastName' => 'Kowalski',
        ];
        $entries = [$model1, $model2];

        $controller = new Controller('id', \Yii::$app);
        $action = new Select2Autocomplete(
            'test',
            $controller,
            [
                'select2Text' => 'firstName',
                'select2Key' => 'id',
                'request' => $this->mockRequest($term),
                'response' => $this->mockResponse(),
                'entriesCallback' => function () use ($entries) {
                    return $entries;
                }
            ]
        );

        $response = $action->run();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertIsArray($response->data);
        $this->assertArrayHasKey('results', $response->data);

        $expectedArray = [
            ['id' => 1, 'text' => 'Marcin'],
            ['id' => 2, 'text' => 'Jan']
        ];
        $this->assertEquals($expectedArray, $response->data['results']);
    }

    public function testRunSelect2AttributesCustomRequestFieldName(): void
    {
        $term = 'asd';
        $fieldName = 'title';
        $entries = [['id' => 1, 'label' => 'asd1'], ['id' => 2, 'label' => 'asd2']];

        $controller = new Controller('id', \Yii::$app);
        $action = new Select2Autocomplete(
            'test',
            $controller,
            [
                'requestFieldName' => $fieldName,
                'request' => $this->mockRequest($term, $fieldName),
                'response' => $this->mockResponse(),
                'entriesCallback' => function () use ($entries) {
                    return $entries;
                }
            ]
        );

        $response = $action->run();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertIsArray($response->data);
        $this->assertArrayHasKey('results', $response->data);

        $expectedArray = [
            ['id' => 1, 'text' => 'asd1'],
            ['id' => 2, 'text' => 'asd2']
        ];
        $this->assertEquals($expectedArray, $response->data['results']);
    }

    protected function mockRequest(string $term, string $fieldName = 'term'): Request
    {
        $request = $this->getMockBuilder(Request::class)
            ->onlyMethods(['get'])
            ->getMock();
        $request->expects($this->once())
            ->method('get')
            ->with($fieldName)
            ->willReturn($term);

        /** @var Request $request */
        return $request;
    }

    protected function mockResponse(): Response
    {
        /** @var Response $response */
        $response = $this->getMockBuilder(Response::class)
            ->getMock();
        return $response;
    }
}

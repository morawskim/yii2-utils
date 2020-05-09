<?php

namespace mmo\yii2\actions;

use ArrayObject;
use Laminas\Diagnostics\Result\Collection;
use Laminas\Diagnostics\Result\FailureInterface;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\SkipInterface;
use Laminas\Diagnostics\Result\SuccessInterface;
use Laminas\Diagnostics\Result\WarningInterface;
use Laminas\Diagnostics\Runner\Runner;
use yii\base\Action;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\helpers\Json;
use yii\web\Response;

class LaminasDiagnostics extends Action
{
    /** @var Response */
    public $response;

    /** @var Runner */
    public $runner;

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        try {
            $this->runner = Instance::ensure($this->runner, Runner::class);
        } catch (InvalidConfigException $e) {
            throw new InvalidConfigException('The "runner" property has wrong configuration.', 0, $e);
        }
        if (!$this->runner instanceof Runner) {
            throw new InvalidConfigException(
                sprintf('The "runner" property must be instance of "%s"', Runner::class)
            );
        }
    }

    public function run(): Response
    {
        $resultCollection = $this->runner->run();

        $data = [
            'details' => $this->getDetails($resultCollection),
            'success' => $resultCollection->getSuccessCount(),
            'warning' => $resultCollection->getWarningCount(),
            'failure' => $resultCollection->getFailureCount(),
            'skip' => $resultCollection->getSkipCount(),
            'unknown' => $resultCollection->getUnknownCount(),
            'passed' => $resultCollection->getFailureCount() === 0,
        ];

        $response = $this->getResponse();
        $response->getHeaders()
            ->set('Cache-Control', 'no-cache')
            ->set('Pragma', 'no-cache')
            ->set('Expires', '0')
            ->set('Content-Type', 'application/json');
        $response->setStatusCode(200);
        $response->format = Response::FORMAT_RAW;
        $response->content = Json::encode($data);
        return $response;
    }

    private function getDetails(Collection $resultCollection): ArrayObject
    {
        $details = new ArrayObject();

        foreach ($resultCollection as $index => $item) {
            $result = $resultCollection[$item];
            $label = sprintf('%d. %s', $index, $item->getLabel());
            $details[$label] = [
                'result' => $this->getResultName($result),
                'message' => $result->getMessage(),
                'data' => $result->getData(),
            ];
        }
        return $details;
    }

    private function getResultName(ResultInterface $result): string
    {
        if ($result instanceof SuccessInterface) {
            return 'success';
        }
        if ($result instanceof WarningInterface) {
            return 'warning';
        }
        if ($result instanceof FailureInterface) {
            return 'failure';
        }
        if ($result instanceof SkipInterface) {
            return 'skip';
        }

        return 'unknown';
    }

    protected function getResponse(): Response
    {
        if (null === $this->response) {
            $this->response = \Yii::$app->getResponse();
        }
        return $this->response;
    }
}
<?php

namespace mmo\yii2\actions;

use yii\base\Action;
use yii\web\View;

class RenderView extends Action
{
    /**
     * @var View the view object that can be used to render views or view files.
     */
    public $view;

    /**
     * @var string the view name.
     */
    public $viewFile;

    /**
     * @var array $params the parameters (name-value pairs) that will be extracted and made available in the view file.
     */
    public $params = [];

    /**
     * @var string the layout file. This can be either an absolute file path or an alias of it.
     */
    public $layout;

    public function run()
    {
        $content = $this->getView()->render($this->viewFile, $this->params, $this);
        return $this->renderContent($content);
    }

    protected function getView()
    {
        if ($this->view === null) {
            $this->view = \Yii::$app->getView();
        }

        return $this->view;
    }

    public function renderContent($content)
    {
        $layoutFile = $this->layout;
        if ($layoutFile !== null) {
            return $this->getView()->renderFile($layoutFile, ['content' => $content], $this);
        }

        return $content;
    }
}

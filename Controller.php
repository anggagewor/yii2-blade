<?php


namespace Anggagewor\Blade;


use yii\base\InvalidConfigException;
use Yii;
class Controller extends \yii\web\Controller
{
    public $layout='main.blade';

    /**
     * @param string $view
     * @param array  $params
     *
     * @return string
     * @throws InvalidConfigException
     */
    public function render($view, $params = [])
    {
        $layoutFile = $this->findLayoutFile($this->getView());

        $layoutExt = pathinfo($layoutFile, PATHINFO_EXTENSION);

        $viewExt = pathinfo($view, PATHINFO_EXTENSION);

        if ($layoutExt === ViewRenderer::EXTENTION && $viewExt === ViewRenderer::EXTENTION)
        {
            $this->getView()->renderers[$layoutExt] = Yii::createObject($this->getView()->renderers[$layoutExt]);

            $this->getView()->renderers[$layoutExt]->addLayout($layoutFile);

            return $this->getView()->render($view, $params, $this);
        }

        return parent::render($view, $params);
    }
}
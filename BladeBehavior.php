<?php


namespace Anggagewor\Blade;

use yii\base\Behavior;
use yii\base\InvalidConfigException;
use yii\base\ViewEvent;
use yii\web\Controller;
use yii\web\View;
use Yii;

class BladeBehavior extends Behavior
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        // Will add an event before rendering the view file.
        Yii::$app->getView()->on(View::EVENT_BEFORE_RENDER, [$this, 'viewRender']);
    }

    /**
     * Changes the render file order of the layout and views
     * if both layout and view are blade files.
     *
     * @param ViewEvent $event
     * @return mixed
     * @throws InvalidConfigException
     */
    public function viewRender($event)
    {

        /** @var View $view */
        $view = $event->sender;

        /** @var Controller $controller */
        $controller = $this->owner;

        $viewFile = $event->viewFile;

        /**
         * The layout file defined for that controller
         *
         * @var String $layoutFile
         */
        $layoutFile = $controller->findLayoutFile($view);

        // If layout is not a string, won't be necessary to do anything more.
        if (!is_string($layoutFile))
        {
            return;
        }

        /** @var String $layoutExt */
        $layoutExt = pathinfo($layoutFile, PATHINFO_EXTENSION);

        /** @var String $viewExt */
        $viewExt = pathinfo($viewFile, PATHINFO_EXTENSION);

        $bladeExtension = $this->getRendererExtension($view);

        if ($layoutExt === $bladeExtension && $viewExt === $bladeExtension)
        {
            $view->renderers[$bladeExtension]->addLayout($layoutFile);
            $controller->layout = FALSE;
        }

    }


    /**
     * Checks for the defined Blade file extension
     * for the renderer. Since we don't know the name
     * the developer has given to the renderer we will have
     * to check it one by one.
     *
     *
     * @param View $view
     * @return string
     * @throws InvalidConfigException
     */
    public function getRendererExtension($view)
    {
        $renderList = $view->renderers;

        $extension = '';

        foreach ($renderList as $key => $renderer)
        {

            if (is_array($renderer) && trim($renderer['class'], '\\') === trim(ViewRenderer::class, '\\'))
            {
                $view->renderers[$key] = Yii::createObject($view->renderers[$key]);
                $extension = $view->renderers[$key]->extension;
            }
            elseif (is_object($renderer) && get_class($renderer) === ViewRenderer::class)
            {
                $extension = $view->renderers[$key]->extension;
            }
        }

        return $extension;
    }
}
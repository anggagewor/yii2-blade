<?php


namespace Anggagewor\Blade;


use Illuminate\Contracts\View\Factory;
use yii\base\View;
use Yii;
use yii\helpers\VarDumper;

class ViewRenderer extends \yii\base\ViewRenderer
{
    /**
     * Extension the special view files will use
     *
     * @var string
     */
    const EXTENTION='blade';
    public $extension = 'blade';
    /**
     * Path where view cache will be located
     * Warning: this path must be writable by PHP.
     *
     * @var  String
     */
    public $cachePath;
    /**
     * List of view paths
     *
     * @var String[]
     */
    public $viewPaths = [];
    /**
     * Blade object used to make the dawgh
     *
     * @var  Blade
     */
    public $blade;
    /**
     * Main view that will be used as layout for the rest of
     * views in this render.
     *
     * If it's null the layout will be the view sent in the
     * render.
     *
     * @var string
     */
    protected $layoutView;


    /**
     * @inheritdoc
     */
    public function init()
    {
        if (is_null($this->cachePath) || !is_string($this->cachePath))
        {
            throw new InvalidParamException('Cache path must be a declared string');
        }

        foreach ($this->viewPaths as $key => $path)
        {
            $this->viewPaths[$key] = Yii::getAlias($path);
        }

        $this->blade = new Blade($this->viewPaths, Yii::getAlias($this->cachePath));

        $this->blade->view()->addExtension($this->extension, $this->extension);
    }


    /**
     * Renders the view file sent by /Yii/base/View
     *
     * @param View   $view
     * @param string $viewFile
     * @param array  $params
     * @return mixed
     */
    public function render($view, $viewFile, $params)
    {

        $viewFile = $this->normalizeView($viewFile);

        if (is_null($this->layoutView))
        {
            $base_view = $viewFile;
            $viewFile = NULL;
        }
        else
        {
            $base_view = $this->layoutView;
            $this->layoutView = NULL;
        }

        $viewObject = $this->blade->view()->make($base_view, $params)->with('view', $view);

        if (!is_null($viewFile))
        {
            $params['view'] = $view;
            $viewObject->nest($viewFile . '_view', $viewFile, $params);
        }
        return $viewObject->render();
    }

    /**
     * Returns View blade Object
     *
     * @return Factory
     */
    public function view()
    {
        return $this->blade->view();
    }


    /**
     * Adds a layout to the Blade System to use as a base view
     *
     * @param $layout
     */
    public function addLayout($layout)
    {
        $this->layoutView = $this->normalizeView($layout);
    }

    /**
     * @param String $view
     * @return mixed
     */
    protected function normalizeView($view)
    {
        $directory = pathinfo($view, PATHINFO_DIRNAME);
        $viewFile = pathinfo($view, PATHINFO_FILENAME);

        $this->blade->view()->getFinder()->addLocation($directory . '/');

        return $viewFile;
    }
}
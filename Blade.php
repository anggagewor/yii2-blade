<?php


namespace Anggagewor\Blade;


use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Compilers\BladeCompiler;
class Blade
{
    /**
     * @var array
     */
    public $viewPaths;
    public $cachePath;
    /**
     * @var Container
     */
    protected $container;
    /**
     * @var Factory
     */
    protected $instance;

    /**
     * Blade constructor.
     *
     * @param array           $viewPaths
     * @param                 $cachePath
     * @param Dispatcher|null $events
     */
    public function __construct($viewPaths = [], $cachePath, Dispatcher $events = null)
    {

        $this->container = new Container;

        $this->viewPaths = (array)$viewPaths;

        $this->cachePath = $cachePath;

        $this->registerFilesystem();

        $this->registerEvents($events ?: new Dispatcher);

        $this->registerEngineResolver();

        $this->registerViewFinder();

        $this->instance = $this->registerFactory();
    }

    public function registerFilesystem()
    {
        $this->container->singleton('files', function () {
            return new Filesystem();
        });
    }

    public function registerEvents(Dispatcher $events)
    {
        $this->container->singleton('events', function () use ($events) {
            return $events;
        });
    }

    public function registerEngineResolver()
    {
        $me = $this;

        $this->container->singleton('view.engine.resolver', function ($app) use ($me) {
            $resolver = new EngineResolver;
            foreach (array('php', 'blade') as $engine) {
                $me->{'register' . ucfirst($engine) . 'Engine'}($resolver);
            }

            return $resolver;
        });
    }

    public function registerViewFinder()
    {
        $me = $this;
        $this->container->singleton('view.finder', function ($app) use ($me) {
            $paths = $me->viewPaths;

            return new FileViewFinder($app['files'], $paths);
        });
    }

    public function registerFactory()
    {
        $resolver = $this->container['view.engine.resolver'];

        $finder = $this->container['view.finder'];

        $env = new Factory($resolver, $finder, $this->container['events']);

        $env->setContainer($this->container);

        return $env;
    }

    /**
     * @param EngineResolver $resolver
     */
    public function registerPhpEngine($resolver)
    {
        $resolver->register('php', function () {
            return new PhpEngine;
        });
    }

    /**
     * @param EngineResolver $resolver
     */
    public function registerBladeEngine($resolver)
    {
        $me = $this;
        $app = $this->container;

        $this->container->singleton('blade.compiler', function ($app) use ($me) {
            $cache = $me->cachePath;

            return new BladeCompiler($app['files'], $cache);
        });

        $resolver->register('blade', function () use ($app) {
            return new CompilerEngine($app['blade.compiler'], $app['files']);
        });
    }

    public function getCompiler()
    {
        return $this->container['blade.compiler'];
    }

    public function view()
    {
        return $this->instance;
    }
}
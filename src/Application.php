<?php

namespace Tanto\Tanto;

use Dotenv\Dotenv;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Contracts\Events\Dispatcher;
use Symfony\Component\Console\Application as SymfonyConsole;

class Application extends Container
{
    protected $console;
    protected $config;

    protected static $version = '0.0.1-alpha';

    public function __construct($basePath = null, $configPath = null)
    {
        self::setInstance($this);

        $basePath = $basePath ?: __DIR__ . '/../../../tanto';
        $configPath = $configPath ?: $basePath.'/config';

        if (file_exists($basePath . '/.env')) {
            $env = new Dotenv($basePath);
            $env->load();
        }

        $this->loadConfigurations($configPath);

        $this->console = new SymfonyConsole('Tanto', self::$version);

        foreach ($this->config->get('register.bindings') as $abstract => $object) {
            $this->bind($abstract, $object);
        }

        foreach ($this->config->get('register.instances') as $abstract => $object) {
            $this->instance($abstract, $object);
        }

        foreach ($this->config->get('register.singletons') as $abstract => $object) {
            $this->singleton($abstract, $object);
        }

        $filesystem = $this->make(Filesystem::class);

        if (! $filesystem->isDirectory($this->config->get('paths.public'))) {
            $filesystem->makeDirectory($this->config->get('paths.public'));
        }

        if (! $filesystem->isDirectory($this->config->get('paths.cache'))) {
            $filesystem->makeDirectory($this->config->get('paths.cache'));
        }

        $dispatcher = app(Dispatcher::class);

        foreach ($this->config->get('events.listeners') as $group => $listeners) {
            if (!is_array($listeners)) {
                $listeners = [$listeners];
            }

            foreach ($listeners as $listener) {
                $dispatcher->listen($group, $this->wrap($listener));
            }
        }
    }

    public function getConfigRepository()
    {
        return $this->config;
    }

    protected function loadConfigurations($configPath)
    {
        $this->config = new Repository(
            $this->readConfig($configPath)
        );
    }

    protected function readConfig($configPath)
    {
        $config = [];
        $filesystem = new Filesystem();

        $files = $filesystem->allFiles($configPath);

        foreach ($files as $file) {
            $parts = explode('.', $file->getFileName());
            $path = $file->getPathName();

            $config[$parts[0]] = require $path;
        }

        return $config;
    }

    protected function registerAbstracts(array $abstracts, $singleton = false)
    {
        foreach ($abstracts as $abstract => $instance) {
            if ($singleton) {
                $this->singleton($abstract, $instance);
            } else {
                $this->bind($abstract, $instance);
            }
        }
    }

    public function handle()
    {
        $this->registerCommands();

        $this->console->run();
    }

    protected function registerCommands()
    {
        foreach ($this->config->get('console.commands') as $command) {
            $this->console->add($this->make($command));
        }
    }
}
<?php

namespace Tanto\Tanto;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Contracts\View\Factory;
use Tanto\Tanto\Contracts\FileHandler;
use Symfony\Component\Finder\SplFileInfo;
use Tanto\Tanto\Contracts\SiteBuilder as SiteBuilderContract;

class SiteBuilder implements SiteBuilderContract
{
    /**
     * The FileSystem instance.
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * The view factory instance.
     *
     * @var Factory
     */
    protected $viewFactory;

    /**
     * The file handler instance.
     *
     * @var FileHandler
     */
    protected $fileHandler;

    /**
     * The file handler instance.
     *
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * SiteBuilder constructor.
     *
     * @param FileHandler $fileHandler
     * @param Factory $viewFactory
     * @param Filesystem $filesystem
     * @param Dispatcher $dispatcher
     */
    public function __construct(FileHandler $fileHandler, Factory $viewFactory, Filesystem $filesystem, Dispatcher $dispatcher)
    {
        $this->filesystem = $filesystem;
        $this->viewFactory = $viewFactory;
        $this->fileHandler = $fileHandler;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Build the site from blade views.
     *
     * @param boolean $forceBuild
     *
     * @return void
     */
    public function build($forceBuild = false)
    {
        $this->filesystem->cleanDirectory(config('paths.public'));

        if ($forceBuild) {
            $this->filesystem->cleanDirectory(config('paths.cache'));
        }

        $this->dispatcher->fire('building');

        $files = $this->getSiteFiles(
            config('paths.content')
        );

        $viewData = $this->buildViewsData();

        $this->handleSiteFiles($files, $viewData);
    }

    /**
     * Handle non-blog site files.
     *
     * @param array $files
     *
     * @return void
     */
    protected function handleSiteFiles($files, $viewData)
    {
        foreach ($files as $file) {
            $this->fileHandler->handle($file, $viewData);
        }
    }

    /**
     * Get the site files that will be converted into pages.
     *
     * @return SplFileInfo[]
     */
    protected function getSiteFiles($publicPath)
    {
        $files = $this->filesystem->allFiles($publicPath);

        $files = $this->filterSiteFiles($files);

        if ($this->filesystem->exists($publicPath.'/.htaccess')) {
            $files[] = new SplFileInfo($publicPath.'/.htaccess', '', '.htaccess');
        }

        return $files;
    }

    /**
     * Filter off files that should not be published.
     *
     * @param SplFileInfo[] $files
     *
     * @return SplFileInfo[]
     */
    protected function filterSiteFiles(array $files)
    {
        $filters = config('content.filters');

        foreach ($filters as $filter) {
            $files = app()->call($filter, ['files' => $files]);
        }

        return $files;
    }

    /**
     * Build array of data to be passed to every view.
     *
     * @return array
     */
    protected function buildViewsData()
    {
        return ['config' => config()];
    }
}

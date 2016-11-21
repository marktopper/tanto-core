<?php

namespace Tanto\Tanto\Compilers;

use Illuminate\Contracts\View\Factory;
use Symfony\Component\Finder\SplFileInfo;

class BladeCompiler extends AbstractCompiler
{
    protected $viewFactory;

    public function __construct(Factory $viewFactory)
    {
        $this->viewFactory = $viewFactory;
    }

    public function compile(SplFileInfo $file)
    {
        return $this->viewFactory->make($this->getViewPath($file), [])->render();
    }

    public function getPublicFilename(SplFileInfo $file)
    {
        return 'index.html';
    }

    /**
     * Get the path of the view.
     *
     * @param SplFileInfo $file
     *
     * @return string
     */
    protected function getViewPath(SplFileInfo $file)
    {
        return str_replace('.blade.php', '', $file->getRelativePathname());
    }
}
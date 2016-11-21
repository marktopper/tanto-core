<?php

namespace Tanto\Tanto\Compilers;

use Illuminate\View\Engines\PhpEngine;
use Illuminate\Contracts\View\Factory;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;
use Tanto\Tanto\Contracts\Markdown;

class MarkdownCompiler extends AbstractCompiler
{
    protected $filesystem;
    protected $viewFactory;
    protected $markdown;
    protected $engine;

    public function __construct(Filesystem $filesystem, Factory $viewFactory, Markdown $markdown, PhpEngine $engine)
    {
        $this->filesystem = $filesystem;
        $this->viewFactory = $viewFactory;
        $this->markdown = $markdown;
        $this->engine = $engine;
    }

    public function compile(SplFileInfo $file)
    {
        list($fileContent, $fileYAML) = $this->parse($file);

        $cached = $this->defineCachedFilename($file);

        $bladeCompiler = $this->viewFactory->getEngineResolver()->resolve('blade')->getCompiler();

        $viewContent = $this->buildBladeViewContent($fileYAML, $fileContent);

        if ($this->isExpired($file, $cached)) {
            $this->filesystem->put($cached, $bladeCompiler->compileString($viewContent));
        }

        return $this->engine->get($cached, $this->createViewData());
    }

    protected function createViewData()
    {
        return ['__env' => $this->viewFactory];
    }

    protected function parse(SplFileInfo $file)
    {
        return $this->markdown->parseWithYAML($file->getContents());
    }

    protected function defineCachedFilename(SplFileInfo $file)
    {
        $markdownCache = config('paths.markdown_cache', config('paths.cache').'/markdown');

        return $markdownCache.'/'.sha1($file->getRelativePathname()).'.php';
    }

    public function getPublicFilename(SplFileInfo $file)
    {
        return 'index.html';
    }

    /**
     * Build the content of the imaginary blade view.
     *
     * @return string
     */
    protected function buildBladeViewContent(array $fileYAML, $fileContent)
    {
        $sections = '';

        foreach ($fileYAML as $name => $value) {
            $sections .= "@section('$name', '".addslashes($value)."')\n\r";
        }

        return
            "@extends('{$fileYAML['view::extends']}')
            $sections
            @section('{$fileYAML['view::yields']}')
            {$fileContent}
            @stop";
    }

    /**
     * Determine if the view at the given path is expired.
     *
     * @return bool
     */
    protected function isExpired(SplFileInfo $file, $cached)
    {
        if (! $this->filesystem->exists($cached)) {
            return true;
        }

        $lastModified = $this->filesystem->lastModified($file->getPath());

        return $lastModified >= $this->filesystem->lastModified($cached);
    }
}
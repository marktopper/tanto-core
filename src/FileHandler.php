<?php

namespace Tanto\Tanto;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Contracts\View\Factory;
use Symfony\Component\Finder\SplFileInfo;
use Tanto\Tanto\Contracts\FileHandler as FileHandlerContract;

class FileHandler implements FileHandlerContract
{
    protected $filesystem;

    protected $viewFactory;

    /**
     * The view file.
     *
     * @var SplFileInfo
     */
    protected $file;

    /**
     * The path to the blade view.
     *
     * @var string
     */
    protected $viewPath;

    /**
     * the path to the generated directory.
     *
     * @var string
     */
    protected $directory;

    /**
     * Data to be passed to every view.
     *
     * @var array
     */
    public $viewsData = [];

    /**
     * AbstractHandler constructor.
     *
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem, Factory $viewFactory)
    {
        $this->filesystem = $filesystem;
        $this->viewFactory = $viewFactory;
    }

    /**
     * Convert a blade view into a site page.
     *
     * @param SplFileInfo $file
     * @param array       $data
     *
     * @return void
     */
    public function handle(SplFileInfo $file, array $data = [])
    {
        $this->file = $file;
        $this->viewsData = $data;
        $this->viewPath = $this->getViewPath();
        $this->directory = $this->getDirectoryPrettyName();

        $this->appendViewInformationToData();

        $content = $this->getFileContent($file);

        $this->filesystem->put(
            sprintf(
                '%s/%s',
                $this->prepareAndGetDirectory(),
                $this->getPublicFilename($file)
            ),
            $content
        );
    }

    protected function getPublicFilename(SplFileInfo $file)
    {
        $compiler = $this->getFileCompiler($file);

        if (!is_null($compiler)) {
            return app()->call([$compiler, 'getPublicFilename'], ['file' => $file]);
        }

        return $file->getFilename();
    }

    /**
     * Get the content of the file after rendering.
     *
     * @param SplFileInfo $file
     *
     * @return string
     */
    protected function getFileContent(SplFileInfo $file)
    {
        $compiler = $this->getFileCompiler($file);

        if (is_null($compiler)) {
            $compiler = app(config('compile.default'));
        }

        return app()->call([$compiler, 'compile'], ['file' => $file]);
    }

    protected function getFileCompiler(SplFileInfo $file)
    {
        $compilers = config('compile.extensions');

        foreach ($compilers as $extension => $compiler) {
            if (ends_with($file->getFilename(), $extension)) {
                return app($compiler);
            }
        }
    }

    /**
     * Render the blade file.
     *
     * @return string
     */
    protected function renderBlade()
    {
        return $this->viewFactory->make($this->viewPath, $this->viewsData)->render();
    }

    /**
     * Render the markdown file.
     *
     * @return string
     */
    protected function renderMarkdown()
    {
        $markdownFileBuilder = new MarkdownFileBuilder($this->filesystem, $this->viewFactory, $this->file, $this->viewsData);

        return $markdownFileBuilder->render();
    }

    /**
     * Prepare and get the directory name for pretty URLs.
     *
     * @return string
     */
    protected function prepareAndGetDirectory()
    {
        if (! $this->filesystem->isDirectory($this->directory)) {
            $this->filesystem->makeDirectory($this->directory, 0755, true);
        }

        return $this->directory;
    }

    /**
     * Generate directory path to be used for the file pretty name.
     *
     * @return string
     */
    protected function getDirectoryPrettyName()
    {
        $fileBaseName = $this->getFileName();

        $fileRelativePath = $this->normalizePath($this->file->getRelativePath());

        if (in_array($this->file->getExtension(), ['php', 'md']) && $fileBaseName != 'index') {
            $fileRelativePath .= $fileRelativePath ? "/$fileBaseName" : $fileBaseName;
        }

        $publicPath = config('paths.public');

        return $publicPath.'/'.($fileRelativePath ? "/$fileRelativePath" : '');
    }

    /**
     * Get the path of the view.
     *
     * @return string
     */
    protected function getViewPath()
    {
        return str_replace(['.blade.php', '.md'], '', $this->file->getRelativePathname());
    }

    /**
     * Get the file name without the extension.
     *
     * @return string
     */
    protected function getFileName(SplFileInfo $file = null)
    {
        $file = $file ?: $this->file;

        return str_replace(['.blade.php', '.php', '.md'], '', $file->getBasename());
    }

    /**
     * Append the view file information to the view data.
     *
     * @return void
     */
    protected function appendViewInformationToData()
    {
        $publicPath = config('paths.public');

        $this->viewsData['currentViewPath'] = $this->viewPath;
        $this->viewsData['currentUrlPath'] = ($path = str_replace($publicPath, '', $this->directory)) ? $path : '/';
    }

    /**
     * Normalize Windows file paths to UNIX style
     *
     * @return string
     */
    protected function normalizePath($path)
    {
        return str_replace("\\", '/', $path);
    }
}
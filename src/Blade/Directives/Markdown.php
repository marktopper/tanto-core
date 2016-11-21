<?php

namespace Tanto\Tanto\Blade\Directives;

use Illuminate\View\Compilers\BladeCompiler;

class Markdown
{
    /**
     * @var BladeCompiler
     */
    protected $bladeCompiler;

    public function __construct(BladeCompiler $bladeCompiler)
    {
        $this->bladeCompiler = $bladeCompiler;
    }

    public function register()
    {
        $this->bladeCompiler->directive('markdown', function () {
            return "<?php echo app('Tanto\\Tanto\\Contracts\\Markdown')->parse(<<<'EOT'";
        });

        $this->bladeCompiler->directive('endmarkdown', function () {
            return "\nEOT\n); ?>";
        });
    }
}
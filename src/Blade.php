<?php

namespace Tanto\Tanto;

use Tanto\Tanto\Contracts\Blade as BladeContract;
use Illuminate\View\Compilers\BladeCompiler;

class Blade implements BladeContract
{
    protected $bladeCompiler;

    /**
     * Blade constructor.
     *
     * @param BladeCompiler $bladeCompiler
     */
    public function __construct(BladeCompiler $bladeCompiler)
    {
        $this->bladeCompiler = $bladeCompiler;

        $directives = config('blade.directives');

        foreach ($directives as $directive) {
            $instance = app($directive);

            $instance->register();
        }
    }

    /**
     * Get the blade compiler after extension.
     *
     * @return BladeCompiler
     */
    public function getCompiler()
    {
        return $this->bladeCompiler;
    }
}
<?php

namespace Tanto\Tanto\Compilers;

use Symfony\Component\Finder\SplFileInfo;

class AbstractCompiler implements CompilerInterface
{
    public function getPublicFilename(SplFileInfo $file)
    {
        return $file->getFilename();
    }
}
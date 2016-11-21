<?php

namespace Tanto\Tanto\Compilers;

use Symfony\Component\Finder\SplFileInfo;

class FileCompiler extends AbstractCompiler
{
    public function compile(SplFileInfo $file)
    {
        return $file->getContents();
    }
}
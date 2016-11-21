<?php

namespace Tanto\Tanto\Filters;

use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

class IgnoreFilter
{
    /**
     * Filter of ignored files.
     *
     * @param SplFileInfo[] $files
     *
     * @return SplFileInfo[]
     */
    public function filter(array $files)
    {
        return array_filter($files, function (SplFileInfo $file) {
            foreach (config('content.ignore') as $pattern) {
                if (Str::is($pattern, $file->getRelativePathname())) {
                    return false;
                }
            }

            return true;
        });
    }
}
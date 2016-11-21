<?php

/**
 * Get application instance or make container element.
 *
 * @param null $key
 *
 * @return \Tanto\Tanto\Application | mixed
 */
function app($key = null)
{
    if (is_null($key)) {
        return Tanto\Tanto\Application::getInstance();
    }

    return Tanto\Tanto\Application::getInstance()->make($key);
}

/**
 * Get configuration
 *
 * @param null $key
 * @param null $default
 * @return \Illuminate\Config\Repository | mixed
 */
function config($key = null, $default = null)
{
    if (is_null($key)) {
        return app()->getConfigRepository();
    }

    return app()->getConfigRepository()->get($key, $default);
}

/**
 * Generate url.
 *
 * @param null|string $expression
 * @return string
 */
function url($expression = null)
{
    $trailingSlash = ! str_contains($expression, '.') ? '/' : '';

    $base_url = config('url.base_url');

    return str_replace(['///', '//'], '/', $base_url.'/'.trim($expression, '/').$trailingSlash);
}

if (! function_exists('env')) {
    /**
     * Gets the value of an environment variable. Supports boolean, empty and null.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function env($key, $default = null)
    {
        $value = getenv($key);

        if ($value === false) {
            return value($default);
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return;
        }

        if (strlen($value) > 1 && \Illuminate\Support\Str::startsWith($value, '"') && \Illuminate\Support\Str::endsWith($value, '"')) {
            return substr($value, 1, -1);
        }

        return $value;
    }
}
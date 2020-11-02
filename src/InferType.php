<?php

namespace TomKeyte\LaravelHttp2Push;

use TomKeyte\LaravelHttp2Push\Exceptions\UnknownResourceTypeException;

class InferType
{

    const FONT_TYPES = ['otf', 'ttf', 'woff', 'woff2'];

    const IMAGE_TYPES = ['apng', 'avif', 'gif', 'jpg', 'jpeg', 'jfif', 'pjpeg', 'pjp', 'png', 'svg', 'webp'];

    /**
     * Infer the type of a resource, e.g script or stype
     *
     * @param String $resource
     */
    public static function infer($resource): ?string
    {
        $ext = self::getExtension($resource);
        if ($ext === "") {
            self::fail("No file type extension on $resource");
        }

        if ($ext === 'js') {
            return "script";
        }

        if ($ext === 'css') {
            return "style";
        }

        if (in_array($ext, self::IMAGE_TYPES)) {
            return "image";
        }

        if (in_array($ext, self::FONT_TYPES)) {
            return "font";
        }

        return self::fail("Unable to determine type for $resource");
    }

    /**
     * Get the extension of a resource
     *
     * @param string $resource
     */
    private static function getExtension($resource): string
    {
        $resource = strtok($resource, '?'); # Trim query parameters
        $ext = strrchr($resource, '.');
        $trimmed = ltrim($ext, ".");
        return strtolower($trimmed);
    }

    /**
     * Fail infering
     *
     * @throws \TomKeyte\LaravelHttp2Push\Exceptions\UnknownResourceTypeException
     */
    private static function fail($resource): void
    {
        throw_if(
            config('http2push.exception_on_failure', true),
            new UnknownResourceTypeException("Unable to determine type for resource $resource")
        );
    }
}

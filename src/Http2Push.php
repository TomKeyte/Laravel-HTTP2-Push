<?php

namespace TomKeyte\LaravelHttp2Push;

use Illuminate\Support\Collection;

class Http2Push
{
    /**
     * The resources to be pushed
     */
    private $resources;

    /**
     * Class Constructor
     */
    public function __construct()
    {
        $this->resources = new Collection();

        $this->addPermanents();
    }

    /**
     * Add a resource to the push stack
     *
     * @param string $resource
     * @param int|null $expires
     */
    public function add($resource, $expires = null): void
    {
        if (!is_null($expires)) {
            $expires = (int)$expires;
        }
        $this->resources->add(new PushResource($resource, $expires));
    }

    /**
     * Add the resources that are always pushed
     */
    private function addPermanents()
    {
        collect(config('http2push.always'))
            ->each(function ($resource) {
                if (is_string($resource)) {
                    $this->add($resource);
                }
                if (is_array($resource) && isset($resource['src'])) {
                    $this->add($resource['src'], $resource['expires'] ?? null);
                }
            });
    }

    /**
     * Build Link header text
     *
     * Filters those that were already pushed/cached
     */
    public function buildLinkHeader(): string
    {
        return $this->toPush()
            ->map(function ($resource) {
                $src = $resource->getSrc();
                $attrs = $resource->getAttrString();
                return sprintf("<%s>; rel=preload; %s", $src, $attrs);
            })
            ->map(function ($resource) {
                return rtrim($resource, ";");
            })
            ->implode(', ');
    }

    /**
     * Filter out any non unique, or those that have already been pushed
     *
     */
    private function toPush(): \Illuminate\Support\Collection
    {
        return $this->resources
            ->unique(function ($resource) {
                return $resource->getSrc();
            })
            ->filter(function ($resource) {
                return !$resource->wasPushed();
            });
    }

    /**
     * Set a cookie to say this resource has been pushed
     *
     * @param \Illuminate\Http\Response $response
     */
    public function setPushCookies($response): void
    {
        $this->toPush()
            ->each(function ($resource) use (&$response) {
                if ($resource->getCookieExpires() !== false) {
                    $response->withCookie(
                        (new PushCookie($resource->getSrc(), $resource->getCookieExpires()))->makeCookie()
                    );
                }
            });
    }

    /**
     * Determine whether there are resources to push
     */
    public function isNotEmpty(): bool
    {
        return $this->resources->isNotEmpty();
    }
}

<?php

namespace TomKeyte\LaravelHttp2Push;

class PushResource
{
    /**
     * Resource URI
     */
    private $src;

    /**
     * How long the resource push cookie should last (days)
     */
    private $cookieExpires;

    /**
     * Type of resource
     */
    private $type;

    /**
     * Class constructor.
     */
    public function __construct($resource, $expires)
    {
        $this->setSrc($resource);
        $this->setType($resource);
        $this->setCookieExpires($expires);
    }

    /**
     * Set the src attribute
     *
     * @param string $resource
     */
    private function setSrc($resource): void
    {
        $this->src = self::makeAbsolute($resource);
    }

    /**
     * Get the src attribute
     */
    public function getSrc(): string
    {
        return $this->src;
    }

    /**
     * Set the cookieExpires attribute
     *
     * @param int $expires The expiry length in days
     */
    private function setCookieExpires($expires): void
    {
        if ($expires === -1) {
            $this->cookieExpires = null;
        } else {
            // check if we were passed a value,
            // check for resource type default
            // use default from config
            $this->cookieExpires = $expires
                ?? config("http2push.cookie_expire_types.$this->type")
                ?? config("http2push.cookie_expires_in");
        }
    }

    /**
     * Get the cookieExpires attribute
     */
    public function getCookieExpires(): ?int
    {
        return $this->cookieExpires;
    }

    /**
     * Set the type attrbute
     *
     * @param string $resouce
     */
    private function setType($resource): void
    {
        $this->type = InferType::infer($resource);
    }

    /**
     * Make an internal URI an absolute path
     *
     * @param string $src
     */
    private static function makeAbsolute($src): string
    {
        return str_replace(config('app.url'), '', $src);
    }

    /**
     * Build the array of push header attriubutes
     */
    private function makeAttrArray(): array
    {
        $attrs = ['as' => $this->type];
        if ($this->type === 'font') {
            $attrs['crossorigin'] = 'anonymous';
        }

        return $attrs;
    }

    /**
     * Build the push header attribute string
     */
    public function getAttrString(): string
    {
        $strs = [];
        foreach ($this->makeAttrArray() as $key => $val) {
            $strs[] = "$key=$val;";
        }
        return implode(" ", $strs);
    }

    /**
     * Determine from a cookie whether the resource was already pushed
     *
     * @param string $resource
     */
    public function wasPushed(): bool
    {
        return (new PushCookie($this->src))->isSet();
    }
}

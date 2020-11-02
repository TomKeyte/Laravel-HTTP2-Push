<?php

namespace TomKeyte\LaravelHttp2Push;

class PushCookie
{
    const DAY_IN_MINUTES = 60 * 24;

    /**
     * The resource
     */
    private $resource;

    /**
     * The cookie prefix
     */
    private $prefix;

    /**
     * An md5 hashed name
     */
    private $name;

    /**
     * Length of the cookie (in days)
     */
    private $expires;

    /**
     * Class Constructor
     */
    public function __construct($resource, $expires = null)
    {
        $this->resource = $resource;
        $this->prefix = config('http2push.cookie_prefix', 'http2_pushed_');
        $this->setName($resource);
        $this->setExpires($expires);
    }

    /**
     * Make the resource cookie
     *
     * @return \Illuminate\Cookie\CookieJar|\Symfony\Component\HttpFoundation\Cookie
     */
    public function makeCookie()
    {
        return cookie($this->getName(), 1, $this->expires * self::DAY_IN_MINUTES);
    }

    /**
     * Set the name of the cookie
     *
     * @param string $resource
     */
    private function setName($resouce): void
    {
        $this->name = $this->prefix . md5($resouce);
    }

    /**
     * Get the name of the cookie
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the cookie expiry
     */
    private function setExpires($expires): void
    {

        if ($expires) {
            $this->expires = $expires;
        } else {
            $type = InferType::infer($this->resource);
            $this->expires = $expires
                ?? config("http2push.cookie_expire_types.$type")
                ?? config("http2push.cookie_expires_in");
        }
    }

    /**
     * Get the cookie expiry
     */
    public function getExpires(): int
    {
        return $this->expires;
    }

    /**
     * Determine whether the cookie is already set
     */
    public function isSet(): bool
    {
        return isset($_COOKIE[$this->name]);
    }
}

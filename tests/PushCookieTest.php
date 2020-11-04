<?php

namespace TomKeyte\LaravelHttp2Push\Tests\Unit;

use TomKeyte\LaravelHttp2Push\PushCookie;
use TomKeyte\LaravelHttp2Push\PushResource;
use Orchestra\Testbench\TestCase;

class PushCookieTest extends TestCase
{

    /**
     * @test
     */
    public function it_hashes_resource_name()
    {
        $name = '/js/app.js?id=abc123';
        $known_hash = '3d3242810adf185b492516b996dd2f27';

        $cookie = new PushCookie($name);

        $this->assertEquals('http2_pushed_' . $known_hash, $cookie->getName());
    }

    /**
     * @test
     */
    public function it_knows_if_the_cookie_was_set()
    {
        $cookie = new PushCookie('/app.js');
        $name = $cookie->getName();
        $_COOKIE[$name] = 1;

        $this->assertTrue($cookie->isSet());
    }

    /**
     * @test
     */
    public function it_doesnt_set_cookie_if_not_desired()
    {
        $resource = new PushResource('app.js', -1);

        $this->assertNull($resource->getCookieExpires());
    }
}

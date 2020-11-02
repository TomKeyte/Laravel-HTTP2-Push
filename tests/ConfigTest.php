<?php

namespace TomKeyte\LaravelHttp2Push\Tests\Feature;

use TomKeyte\LaravelHttp2Push\Http2PushServiceProvider;
use TomKeyte\LaravelHttp2Push\PushCookie;
use Orchestra\Testbench\TestCase;

class ConfigTest extends TestCase
{
    /**
     * Override Orchestra TestCase method
     */
    protected function getPackageProviders($app)
    {
        return [Http2PushServiceProvider::class];
    }

    /** @test */
    public function it_respects_cookie_prefix()
    {
        $prefix = 'test_prefix_';
        $this->app['config']->set('http2push.cookie_prefix', $prefix);

        $this->assertStringStartsWith($prefix, (new PushCookie('app.js'))->getName());
    }

    /** @test */
    public function it_respects_global_cookie_expiry()
    {
        $expiry = 27;
        $this->app['config']->set('http2push.cookie_expires_in', $expiry);

        $this->assertEquals($expiry, (new PushCookie('/app.js'))->getExpires());
    }

    /** @test */
    public function it_respects_type_specific_expiry()
    {
        $script_expiry = 17;
        $this->app['config']->set('http2push.cookie_expire_types.script', $script_expiry);

        $this->assertEquals($script_expiry, (new PushCookie('/app.js'))->getExpires());
    }
}

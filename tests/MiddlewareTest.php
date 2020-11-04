<?php

namespace TomKeyte\LaravelHttp2Push\Tests;

use Illuminate\Http\Request;
use TomKeyte\LaravelHttp2Push\Http2PushServiceProvider;
use TomKeyte\LaravelHttp2Push\Middleware\AddLinkHeader;
use TomKeyte\LaravelHttp2Push\PushCookie;
use Orchestra\Testbench\TestCase;

class MiddlewareTest extends TestCase
{

    /**
     * Override Orchestra TestCase method
     */
    protected function getPackageProviders($app)
    {
        return [Http2PushServiceProvider::class];
    }

    protected function setUp(): void
    {
        parent::setUp();

        // have to empty cookie array between runs
        $_COOKIE = [];

        $this->response = $this->get('/');
        $this->request = Request::create('/', 'GET');
        $this->push = $this->app->get('http2push');
        $this->push->add('/js/app.js');
    }


    /**
     * @test
     */
    public function a_response_has_the_push_cookie()
    {
        (new AddLinkHeader)
            ->handle(
                $this->request,
                function () {
                    return $this->response;
                }
            )
            ->assertCookie((new PushCookie('/js/app.js'))->getName());
    }

    /**
     * @test
     */
    public function a_response_has_the_link_header()
    {
        (new AddLinkHeader)
            ->handle(
                $this->request,
                function () {
                    return $this->response;
                }
            )
            ->assertHeader('Link', $this->push->buildLinkHeader());
    }
}

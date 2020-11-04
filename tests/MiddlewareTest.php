<?php

namespace TomKeyte\LaravelHttp2Push\Tests;

use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use TomKeyte\LaravelHttp2Push\Http2PushServiceProvider;
use TomKeyte\LaravelHttp2Push\Middleware\AddLinkHeader;
use TomKeyte\LaravelHttp2Push\PushCookie;
use Orchestra\Testbench\TestCase;
use stdClass;

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

        $this->response = new Response();
        $this->request = new Request();
        $this->push = $this->app->get('http2push');
        $this->push->add('/js/app.js');
    }

    /**
     * Converts a base response class into a test response
     */
    private function toTestResponse($response)
    {
        return TestResponse::fromBaseResponse($response);
    }

    /**
     * @test
     */
    public function a_response_has_the_link_header()
    {
        $this->toTestResponse(
            (new AddLinkHeader)
                ->handle(
                    $this->request,
                    function () {
                        return $this->response;
                    }
                )
        )
            ->assertHeader('Link', $this->push->buildLinkHeader());
    }

    /**
     * @test
     */
    public function a_response_has_the_push_cookie()
    {
        $this->toTestResponse(
            (new AddLinkHeader)
                ->handle(
                    $this->request,
                    function () {
                        return $this->response;
                    }
                )
        )
            ->assertCookie((new PushCookie('/js/app.js'))->getName());
    }

    /**
     * @test
     */
    public function it_does_not_set_link_header_for_json_request()
    {
        $jsonRequest = (new Request());
        $jsonRequest->headers->set('Content-Type', 'application/json', true);

        $this->toTestResponse(
            (new AddLinkHeader)
                ->handle(
                    $jsonRequest,
                    function () {
                        return $this->response;
                    }
                )
        )
            ->assertHeaderMissing('Link');
    }

    /**
     * @test
     */
    public function it_does_not_set_link_header_for_redirect_response()
    {
        $redirectResponse = new Response('', 302);

        $this->toTestResponse(
            (new AddLinkHeader)
                ->handle(
                    $this->request,
                    function () use ($redirectResponse) {
                        return $redirectResponse;
                    }
                )
        )
            ->assertHeaderMissing('Link');
    }

    /**
     * @test
     */
    public function it_does_not_set_link_header_for_a_binary_response()
    {
        $binaryResponse = response()->streamDownload(
            function () {
                return ['data'];
            }
        );

        $this->toTestResponse(
            (new AddLinkHeader)
                ->handle(
                    $this->request,
                    function () use ($binaryResponse) {
                        return $binaryResponse;
                    }
                )
        )
            ->assertHeaderMissing('Link');
    }

    /**
     * @test
     */
    public function it_logs_an_error_if_middleware_throws_exception()
    {
        Log::shouldReceive('error');

        (new AddLinkHeader)
            ->handle(
                (new stdClass),
                function () {
                    return $this->response;
                }
            );
    }
}

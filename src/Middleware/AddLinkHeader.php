<?php

namespace TomKeyte\LaravelHttp2Push\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Throwable;

class AddLinkHeader
{
    /**
     * The request object
     */
    protected $request;

    /**
     * The response object
     */
    protected $response;

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return \Illuminate\Http\Response
     */
    public function handle($request, Closure $next)
    {
        $this->request = $request;
        $this->response = $next($request);

        try {
            if ($this->isCorrectRequestType() && $this->isCorrectResponseType()) {
                $this->pushResources();
            }
        } catch (Throwable $t) {
            Log::error($t);
        }

        return $this->response;
    }

    /**
     * Check that the request is of the correct type for a HTTP push
     */
    private function isCorrectRequestType(): bool
    {
        return $this->request->method() === 'GET' && !$this->request->isJson();
    }

    /**
     * Check that the response is of the correct type for a HTTP push
     */
    private function isCorrectResponseType(): bool
    {
        return ($this->response instanceof \Illuminate\Http\Response && !$this->response->isRedirection());
    }

    /**
     * Add the Link header to the response
     */
    private function pushResources(): void
    {
        $http2push = resolve('http2push');

        if ($http2push->isNotEmpty()) {
            $this->response->header('Link', $http2push->buildLinkHeader(), false);
            $http2push->setPushCookies($this->response);
        }
    }
}

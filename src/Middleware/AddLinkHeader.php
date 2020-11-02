<?php

namespace TomKeyte\LaravelHttp2Push\Middleware;

use Closure;

class AddLinkHeader
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     *
     * @return \Illuminate\Http\Response
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $http2push = resolve('http2push');

        if ($request->method() === 'GET' && $http2push->isNotEmpty()) {
            $response->header('Link', $http2push->buildLinkHeader(), false);
            $http2push->setPushCookies($response);
        }

        return $response;
    }
}

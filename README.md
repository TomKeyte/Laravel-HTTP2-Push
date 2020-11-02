A cache-aware HTTP2 Server Push implementation, for the Laravel framework

[![Latest Version on Packagist](https://img.shields.io/packagist/v/tomkeyte/laravel-http2-push.svg?style=flat-square)](https://packagist.org/packages/tomkeyte/laravel-http2-push)
[![Total Downloads](https://img.shields.io/packagist/dt/tomkeyte/laravel-http2-push.svg?style=flat-square)](https://packagist.org/packages/tomkeyte/laravel-http2-push)

HTTP2 Push is hard! This package makes it easy, providing a simple blade directive that informs clients "Hey, I know you've only asked for a HTML document, but I already know you'll need this stylesheet, so just start downloading them now".

Resources are pushed once per cached-lifetime.

i.e, say you have a stylesheet with a cache length set via the `Cache-Control` header, with an expiry length of 28 days.
The package will initially push the resource, and also set a cookie to track (per resource) whether it has been sent, and therefore cached - after all, there's no need to push a resource that the browser has already cached (Hence the motivation behind this package).

## Installation

Install the package via composer:

```bash
composer require tomkeyte/laravel-http2-push
```

Publish the configuration (recommended):

```bash
php artisan vendor:publish --provider="TomKeyte\LaravelHttp2Push\Http2PushServiceProvider"
```

### Server config

**IMPORTANT! HTTP2 Push via the Link header must be enabled for your webserver**

Instructions for [nginx](https://www.nginx.com/blog/nginx-1-13-9-http2-server-push/#configuring) and [apache](https://httpd.apache.org/docs/2.4/mod/mod_http2.html#h2push).

## Usage

Check the `http2push.php` config file to set default expiry lengths based on your setup. N.B, expiry lengths throughout the package are expressed in days. The setup is as following:

* Set the global cookie expiry limit with `cookie_expires_in`
* Set the cookie expiry for certain file types (these override the global limit):
```php
'cookie_expire_types' => [
    'font' => 90,
    'script' => 27,
    'style' => 24,
    'image' => 30,
],
```
* Optionally, supply an array of resources that should be pushed on every request*
```php
'always' => [
    '/js/app.js', # A simple string
    [
        'src' => '/css/app.css', # Or an array, containing the src & expiry time
        'expires' => '90',
    ],
],
```

In your blade templates, push the resources:

```php
// use default cookie expiry lengths
@h2push('/css/app.css')
```

```php
// specify a cookie expiry length of 60 days, for this resource only
@h2push('/js/app.js', 60)
```

It is recommended you version your resources, so that the package can track whether it needs to re-push them. This package works well with [laravel-mix](https://github.com/JeffreyWay/laravel-mix)

```php
@h2push( mix('/css/app.css') )
```

\* *Every GET request that is routed through the web middleware*

### Testing

``` bash
composer test
```

Generate a code coverage report with

```
composer test-coverage
```

*Requires an applicable code coverage driver, such as xdebug*

## Credits

- [Tom Keyte](https://github.com/tomkeyte)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

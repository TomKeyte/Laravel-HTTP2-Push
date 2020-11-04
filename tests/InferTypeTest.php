<?php

namespace TomKeyte\LaravelHttp2Push\Tests\Unit;

use TomKeyte\LaravelHttp2Push\Exceptions\UnknownResourceTypeException;
use TomKeyte\LaravelHttp2Push\InferType;
use Orchestra\Testbench\TestCase;

class InferTypeTest extends TestCase
{
    /**
     * @test
     */
    public function it_correctly_inferes_basic_types()
    {
        $basic = [
            'script' => 'js/script.js',
            'style' => 'css/app.css'
        ];

        collect($basic)->each(
            function ($resource, $type) {
                $this->assertEquals($type, InferType::infer($resource));
            }
        );
    }

    /**
     * @test
     */
    public function it_correctly_inferes_font_types()
    {
        $fonts = [
            'font.otf',
            'font.ttf',
            'font.woff',
            'font.woff2'
        ];

        collect($fonts)->each(
            function ($resource) {
                $this->assertEquals('font', InferType::infer($resource));
            }
        );
    }

    /**
     * @test
     */
    public function it_correctly_inferes_image_types()
    {
        $images = [
            'image.apng',
            'image.avif',
            'image.gif',
            'image.jpg',
            'image.jpeg',
            'image.jfif',
            'image.pjpeg',
            'image.pjp',
            'image.png',
            'image.svg',
            'image.webp'
        ];

        collect($images)->each(
            function ($resource) {
                $this->assertEquals('image', InferType::infer($resource));
            }
        );
    }

    /**
     * @test
     */
    public function it_inferes_type_when_a_query_string_is_present()
    {
        $resource = 'js/app.js?id=abc123';
        $this->assertEquals('script', InferType::infer($resource));
    }

    /**
     * @test
     */
    public function it_inferes_type_when_resource_contains_multiple_periods()
    {
        $resource = 'js/app.bundle.production.js';
        $this->assertEquals('script', InferType::infer($resource));
    }

    /**
     * @test
     */
    public function it_throws_an_exception_if_it_cannot_infer_type()
    {
        $this->app['config']->set('exception_on_failure');

        $this->expectException(UnknownResourceTypeException::class);
        InferType::infer('script.exe');
    }

    /**
     * @test
     */
    public function it_throws_an_exception_if_no_type_supplied()
    {
        $this->app['config']->set('http2push.exception_on_failure', true);

        $this->expectException(UnknownResourceTypeException::class);
        InferType::infer('file');
    }

    /**
     * @test
     */
    public function it_doesnt_throw_an_exception_if_not_desired()
    {
        $this->app['config']->set('http2push.exception_on_failure', false);

        try {
            InferType::infer('file');
            // fake way of passing test, since there is no pass method,
            // but we just need to check that an exception was NOT thrown
            $this->assertTrue(true);
        } catch (UnknownResourceTypeException $e) {
            $this->fail("Exception was thrown when it shouldn't have been");
        }
    }
}

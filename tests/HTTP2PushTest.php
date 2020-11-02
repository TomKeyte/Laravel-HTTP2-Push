<?php

namespace TomKeyte\LaravelHttp2Push\Tests\Feature;

use TomKeyte\LaravelHttp2Push\Http2Push;
use TomKeyte\LaravelHttp2Push\Http2PushServiceProvider;
use TomKeyte\LaravelHttp2Push\PushCookie;
use Orchestra\Testbench\TestCase;
use ReflectionClass;

class HTTP2PushTest extends TestCase
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

        $this->push = $this->app->get('http2push');
    }


    /** @test */
    public function it_registers()
    {
        $this->assertInstanceOf(Http2Push::class, $this->push);
    }

    /** @test */
    public function it_adds_resources()
    {

        $this->push->add('/js/app.js');
        $this->assertTrue($this->push->isNotEmpty());
    }

    /** @test */
    public function it_builds_the_header_correctly()
    {

        $this->push->add('/js/app.js');

        $this->assertEquals('</js/app.js>; rel=preload; as=script', $this->push->buildLinkHeader());;

        $this->push->add('/css/app.css');

        $this->assertEquals(
            '</js/app.js>; rel=preload; as=script, </css/app.css>; rel=preload; as=style',
            $this->push->buildLinkHeader()
        );
    }

    /** @test */
    public function it_ignores_duplicates()
    {

        $this->push->add('/js/app.js');
        $this->push->add('/js/app.js');
        $this->push->add('/js/app.js', 60);

        $this->assertEquals('</js/app.js>; rel=preload; as=script', $this->push->buildLinkHeader());
    }

    /** @test */
    public function it_ignores_cached_resources()
    {
        $resource = '/js/app.js';
        $cookie = new PushCookie($resource);

        $_COOKIE[$cookie->getName()] = 1;

        $this->push->add($resource);
        $this->assertEmpty($this->push->buildLinkHeader());
    }

    /** @test */
    public function it_renders_the_blade_directive()
    {
        $compiled = resolve('blade.compiler')->compileString("@h2push('app.js')");
        $this->assertEquals("<?php echo h2push('app.js'); ?>", $compiled);
    }

    /** @test */
    public function the_helper_returns_the_resource_uri()
    {
        $this->assertEquals('/js/app.js', h2push('/js/app.js'));

    }

    /** @test */
    public function the_helper_function_adds_a_resource()
    {
        h2push('/js/app.js');

        $this->assertTrue($this->push->isNotEmpty());
    }

    /** @test */
    public function it_adds_config_always_resources()
    {
        $this->app['config']->set('http2push.always', [
            'app.js',
            [
                'src' => 'app.css',
                'expires' => '90',
            ],
        ]);

        // create a fresh object
        // as config has changed
        $push = new Http2Push;

        $this->assertTrue($push->isNotEmpty());
    }

    /** @test */
    public function it_contains_crossorigin_attribute_for_fonts()
    {
        $this->push->add('assets/font.otf');
        $this->assertStringContainsString('crossorigin=anonymous', $this->push->buildLinkHeader());
    }
}

<?php

uses(\PKP\tests\classes\core\PKPRouterTestCase::class);
use Mockery\MockInterface;
use PKP\core\Core;
use PKP\core\PKPPageRouter;
use PKP\security\Validation;


beforeEach(function () {
    $this->router = $this->getMockBuilder(PKPPageRouter::class)
        ->onlyMethods(['getCacheablePages'])
        ->getMock();
    $this->router->expects($this->any())
        ->method('getCacheablePages')
        ->will($this->returnValue(['cacheable']));
});

test('is cacheable not installed', function () {
    $this->setTestConfiguration('request2', 'classes/core/config');
    // not installed
    $mockApplication = $this->_setUpMockEnvironment();
    self::assertFalse($this->router->isCacheable($this->request));
});

test('is cacheable with post', function () {
    $this->setTestConfiguration('request1', 'classes/core/config');
    // installed
    $mockApplication = $this->_setUpMockEnvironment();
    $_POST = ['somevar' => 'someval'];
    self::assertFalse($this->router->isCacheable($this->request));
});

test('is cacheable with pathinfo', function () {
    $this->setTestConfiguration('request1', 'classes/core/config');
    // installed
    $mockApplication = $this->_setUpMockEnvironment();
    $_GET = ['somevar' => 'someval'];
    $_SERVER = [
        'PATH_INFO' => '/context1/somepage',
        'SCRIPT_NAME' => '/index.php',
    ];
    self::assertFalse($this->router->isCacheable($this->request));

    $_GET = [];
    self::assertFalse($this->router->isCacheable($this->request));
});

test('is cacheable with pathinfo success', function () {
    // Creates a mocked Validation only for this test (due to the @runInSeparateProcess)
    $mockValidation = new class () {
        function __construct(public bool $isLogged = false)
        {
            /** @var MockInterface */
            $mock = Mockery::mock('overload:' . Validation::class);
            $mock->shouldReceive('isLoggedIn')->andReturnUsing(fn () => $this->isLogged);
        }
    };
    $this->setTestConfiguration('request1', 'classes/core/config');
    // installed
    $mockApplication = $this->_setUpMockEnvironment();
    $_GET = [];
    $_SERVER = [
        'PATH_INFO' => '/context1/cacheable',
        'SCRIPT_NAME' => '/index.php',
    ];

    self::assertTrue($this->router->isCacheable($this->request, true));
    $mockValidation->isLogged = true;
    self::assertFalse($this->router->isCacheable($this->request, true));
});

test('get cache filename with pathinfo', function () {
    $mockApplication = $this->_setUpMockEnvironment();
    $_SERVER = [
        'PATH_INFO' => '/context1/index',
        'SCRIPT_NAME' => '/index.php',
    ];
    $expectedId = '/context1/index-en';
    self::assertEquals(Core::getBaseDir() . '/cache/wc-' . md5($expectedId) . '.html', $this->router->getCacheFilename($this->request));
});

test('get requested page with pathinfo', function () {
    $mockApplication = $this->_setUpMockEnvironment();

    $_SERVER = [
        'PATH_INFO' => '/context1/some#page',
        'SCRIPT_NAME' => '/index.php',
    ];
    self::assertEquals('somepage', $this->router->getRequestedPage($this->request));
});

test('get requested page with emtpy page', function () {
    $mockApplication = $this->_setUpMockEnvironment();

    $_SERVER = [
        'PATH_INFO' => '/context1',
        'SCRIPT_NAME' => '/index.php',
    ];
    self::assertEquals('', $this->router->getRequestedPage($this->request));
});

test('get requested op with pathinfo', function () {
    $mockApplication = $this->_setUpMockEnvironment();

    $_SERVER = [
        'PATH_INFO' => '/context1/somepage/some#op',
        'SCRIPT_NAME' => '/index.php',
    ];
    self::assertEquals('someop', $this->router->getRequestedOp($this->request));
});

test('get requested op with empty op', function () {
    $mockApplication = $this->_setUpMockEnvironment();

    $_SERVER = [
        'PATH_INFO' => '/context1/somepage',
        'SCRIPT_NAME' => '/index.php',
    ];
    self::assertEquals('index', $this->router->getRequestedOp($this->request));
});

test('url with pathinfo', function () {
    $this->setTestConfiguration('request1', 'classes/core/config');
    // restful URLs
    $mockApplication = $this->_setUpMockEnvironment();
    $_SERVER = [
        'SERVER_NAME' => 'mydomain.org',
        'SCRIPT_NAME' => '/index.php',
        'PATH_INFO' => '/current-context1/current-page/current-op'
    ];

    // Simulate context DAOs
    $this->_setUpMockDAOs();

    $result = $this->router->url($this->request);
    self::assertEquals('http://mydomain.org/index.php/current-context1/current-page/current-op', $result);

    $this->_setUpMockDAOs('new-context1', true);
    $result = $this->router->url($this->request, 'new-context1');
    self::assertEquals('http://mydomain.org/index.php/new-context1', $result);

    $this->_setUpMockDAOs('new?context1', true);
    $result = $this->router->url($this->request, 'new?context1');
    self::assertEquals('http://mydomain.org/index.php/new%3Fcontext1', $result);

    $result = $this->router->url($this->request, null, 'new-page');
    self::assertEquals('http://mydomain.org/index.php/current-context1/new-page', $result);

    $result = $this->router->url($this->request, null, null, 'new-op');
    self::assertEquals('http://mydomain.org/index.php/current-context1/current-page/new-op', $result);

    $result = $this->router->url($this->request, 'new-context1', 'new-page');
    self::assertEquals('http://mydomain.org/index.php/new-context1/new-page', $result);

    $result = $this->router->url($this->request, 'new-context1', 'new-page', 'new-op');
    self::assertEquals('http://mydomain.org/index.php/new-context1/new-page/new-op', $result);

    $result = $this->router->url($this->request, 'new-context1', null, 'new-op');
    self::assertEquals('http://mydomain.org/index.php/new-context1/index/new-op', $result);

    $result = $this->router->url($this->request, 'new-context1', null, null, 'add?path');
    self::assertEquals('http://mydomain.org/index.php/new-context1/index/index/add%3Fpath', $result);

    $result = $this->router->url($this->request, 'new-context1', null, null, ['add-path1', 'add?path2']);
    self::assertEquals('http://mydomain.org/index.php/new-context1/index/index/add-path1/add%3Fpath2', $result);

    $result = $this->router->url(
        $this->request,
        'new-context1',
        null,
        null,
        null,
        [
            'key1' => 'val1?',
            'key2' => ['val2-1', 'val2?2']
        ]
    );
    self::assertEquals('http://mydomain.org/index.php/new-context1?key1=val1%3F&key2[]=val2-1&key2[]=val2%3F2', $result);

    $result = $this->router->url($this->request, 'new-context1', null, null, null, null, 'some?anchor');
    self::assertEquals('http://mydomain.org/index.php/new-context1#someanchor', $result);

    $result = $this->router->url($this->request, 'new-context1', null, null, null, null, 'some/anchor');
    self::assertEquals('http://mydomain.org/index.php/new-context1#some/anchor', $result);

    $result = $this->router->url($this->request, 'new-context1', null, 'new-op', 'add-path', ['key' => 'val'], 'some-anchor');
    self::assertEquals('http://mydomain.org/index.php/new-context1/index/new-op/add-path?key=val#some-anchor', $result);

    $result = $this->router->url($this->request, 'new-context1', null, null, null, ['key1' => 'val1', 'key2' => 'val2'], null, true);
    self::assertEquals('http://mydomain.org/index.php/new-context1?key1=val1&amp;key2=val2', $result);
});

test('url with pathinfo and overridden base url', function () {
    $this->setTestConfiguration('request1', 'classes/core/config');

    // contains overridden context
    // Set up a request with an overridden context
    $mockApplication = $this->_setUpMockEnvironment();
    $_SERVER = [
        'SERVER_NAME' => 'mydomain.org',
        'SCRIPT_NAME' => '/index.php',
        'PATH_INFO' => '/overridden-context/current-page/current-op'
    ];
    $this->_setUpMockDAOs('overridden-context');
    $result = $this->router->url($this->request);
    self::assertEquals('http://some-domain/xyz-context/current-page/current-op', $result);
});

test('url with pathinfo and overridden new context', function () {
    $this->setTestConfiguration('request1', 'classes/core/config');

    // contains overridden context
    // Same set-up as in testUrlWithPathinfoAndOverriddenBaseUrl()
    // but this time use a request with non-overridden context and
    // 'overridden-context' as new context. (Reproduces #5118)
    $mockApplication = $this->_setUpMockEnvironment();
    $_SERVER = [
        'SERVER_NAME' => 'mydomain.org',
        'SCRIPT_NAME' => '/index.php',
        'PATH_INFO' => '/current-context1/current-page/current-op'
    ];
    $this->_setUpMockDAOs('current-context1', true);
    $result = $this->router->url($this->request, 'overridden-context', 'new-page');
    self::assertEquals('http://some-domain/xyz-context/new-page', $result);
});

test('url with locale', function () {
    $mockApplication = $this->_setUpMockEnvironment();
    $_SERVER = [
        'SERVER_NAME' => 'mydomain.org',
        'SCRIPT_NAME' => '/index.php',
        'PATH_INFO' => '/current-context1/current-page/current-op',
    ];
    $this->_setUpMockDAOs(supportedLocales: ['en', 'es']);

    // Add locale to url
    $result = $this->router->url($this->request);
    self::assertEquals('http://mydomain.org/index.php/current-context1/en/current-page/current-op', $result);

    // Override current locale
    $result = $this->router->url($this->request, urlLocaleForPage: 'es');
    self::assertEquals('http://mydomain.org/index.php/current-context1/es/current-page/current-op', $result);
});

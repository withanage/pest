<?php

uses(\PKP\tests\PKPTestCase::class);
use APP\core\Request;
use PKP\core\PKPRequest;
use PKP\core\Registry;
use PKP\plugins\Hook;

/**
 * @see PKPTestCase::getMockedRegistryKeys()
 */
function getMockedRegistryKeys() : array
{
    return [...getMockedRegistryKeys(), 'configData'];
}

beforeEach(function () {
    Hook::rememberCalledHooks();
    $this->request = new Request();
});

afterEach(function () {
    Hook::resetCalledHooks();
});

test('is restful urls enabled1', function () {
    $this->setTestConfiguration('request1', 'classes/core/config');
    self::assertFalse($this->request->isRestfulUrlsEnabled());
});

test('is restful urls enabled2', function () {
    $this->setTestConfiguration('request2', 'classes/core/config');
    self::assertTrue($this->request->isRestfulUrlsEnabled());
});

test('redirect url', function () {
    Hook::add('Request::redirect', redirectUrlHook(...));
    $this->request->redirectUrl('http://some.url/');
    self::assertEquals(
        [['Request::redirect', ['http://some.url/']]],
        Hook::getCalledHooks()
    );
    Hook::clear('Request::redirect');
});

/**
 * A hook for redirection testing.
 *
 * @param string $hookName
 * @param array $args
 */
function redirectUrlHook($hookName, $args)
{
    // Returning true will avoid actual redirection.
    return true;
}

test('get base url', function () {
    $this->setTestConfiguration('request1', 'classes/core/config');
    // baseurl1
    $_SERVER = [
        'SCRIPT_NAME' => '/index.php',
    ];
    self::assertEquals('http://baseurl1/', $this->request->getBaseUrl());

    // Two hooks should have been triggered.
    self::assertEquals(
        [
            ['Request::getServerHost', [false, false, true]],
            ['Request::getBaseUrl', ['http://baseurl1/']]
        ],
        Hook::getCalledHooks()
    );

    // Calling getBaseUrl twice should return the same
    // result without triggering the hooks again.
    Hook::resetCalledHooks();
    self::assertEquals('http://baseurl1/', $this->request->getBaseUrl());
    self::assertEquals(
        [],
        Hook::getCalledHooks()
    );
});

test('get base url with host detection', function () {
    $this->setTestConfiguration('request1', 'classes/core/config');
    $_SERVER = [
        'SERVER_NAME' => 'hostname',
        'SCRIPT_NAME' => '/some/base/path'
    ];
    self::assertEquals('http://hostname/some/base/path', $this->request->getBaseUrl());
});

test('get base path', function () {
    $_SERVER = [
        'SCRIPT_NAME' => '/some/base/path'
    ];
    self::assertEquals('/some/base/path', $this->request->getBasePath());

    // The hook should have been triggered once.
    self::assertEquals(
        [['Request::getBasePath', ['/some/base/path']]],
        Hook::getCalledHooks()
    );

    // Calling getBasePath twice should return the same
    // result without triggering the hook again.
    Hook::resetCalledHooks();
    self::assertEquals('/some/base/path', $this->request->getBasePath());
    self::assertEquals(
        [],
        Hook::getCalledHooks()
    );
});

test('get empty base path', function () {
    $_SERVER = [
        'SCRIPT_NAME' => '/main'
    ];
    self::assertEquals('/main', $this->request->getBasePath());
});

test('get request path', function () {
    $_SERVER = [
        'SCRIPT_NAME' => 'some/script/name'
    ];
    $this->setTestConfiguration('request1', 'classes/core/config');

    // no restful URLs
    self::assertEquals('some/script/name', $this->request->getRequestPath());

    // The hook should have been triggered once.
    self::assertEquals(
        [['Request::getRequestPath', ['some/script/name']]],
        Hook::getCalledHooks()
    );

    // Calling getRequestPath() twice should return the same
    // result without triggering the hook again.
    Hook::resetCalledHooks();
    self::assertEquals('some/script/name', $this->request->getRequestPath());
    self::assertEquals(
        [],
        Hook::getCalledHooks()
    );
});

test('get request path restful', function () {
    $_SERVER = [
        'SCRIPT_NAME' => 'some/script/name'
    ];
    $this->setTestConfiguration('request2', 'classes/core/config');

    // restful URLs
    self::assertEquals('some/script/name', $this->request->getRequestPath());
});

test('get request path with pathinfo', function () {
    $_SERVER = [
        'SCRIPT_NAME' => 'some/script/name',
        'PATH_INFO' => '/extra/path'
    ];
    $this->setTestConfiguration('request1', 'classes/core/config');

    // path info enabled
    self::assertEquals('some/script/name/extra/path', $this->request->getRequestPath());
});

test('get server host localhost', function () {
    // if none of the server variables is set then return the default
    $_SERVER = [
        'SCRIPT_NAME' => '/index.php',
    ];
    self::assertEquals('localhost', $this->request->getServerHost());
});

test('get server host with hostname', function () {
    // if SERVER_NAME is set then return it
    $_SERVER = [
        'SERVER_NAME' => 'hostname',
        'SCRIPT_NAME' => ''
    ];
    self::assertEquals('hostname', $this->request->getServerHost());
})->depends('get server host localhost');

test('get server host with server name', function () {
    // if SERVER_NAME is set then return it
    $_SERVER = [
        'SERVER_NAME' => 'hostname',
        'SCRIPT_NAME' => '/index.php',
    ];
    self::assertEquals('hostname', $this->request->getServerHost());
})->depends('get server host localhost');

test('get server host with http host', function () {
    // if HTTP_HOST is set then return it
    $_SERVER = [
        'SERVER_NAME' => 'hostname',
        'HTTP_HOST' => 'http_host',
        'SCRIPT_NAME' => '/index.php',
    ];
    self::assertEquals('http_host', $this->request->getServerHost());
})->depends('get server host with hostname');

test('get server host with http x forwarded host', function () {
    // if HTTP_X_FORWARDED_HOST is set then return it
    $_SERVER = [
        'SERVER_NAME' => 'hostname',
        'HTTP_HOST' => 'http_host',
        'HTTP_X_FORWARDED_HOST' => 'x_host',
        'SCRIPT_NAME' => '/index.php',
    ];
    self::assertEquals('x_host', $this->request->getServerHost());
})->depends('get server host with http host');

test('get protocol no https variable', function () {
    $_SERVER = [];
    self::assertEquals('http', $this->request->getProtocol());

    // The hook should have been triggered once.
    self::assertEquals(
        [['Request::getProtocol', ['http']]],
        Hook::getCalledHooks()
    );

    // Calling getProtocol() twice should return the same
    // result without triggering the hook again.
    Hook::resetCalledHooks();
    self::assertEquals('http', $this->request->getProtocol());
    self::assertEquals(
        [],
        Hook::getCalledHooks()
    );
});

test('get protocol https variable off', function () {
    $_SERVER = [
        'HTTPS' => 'OFF',
        'SCRIPT_NAME' => '/index.php',
    ];
    self::assertEquals('http', $this->request->getProtocol());
});

test('get protocol https variable on', function () {
    $_SERVER = [
        'HTTPS' => 'ON',
        'SCRIPT_NAME' => '/index.php',
    ];
    self::assertEquals('https', $this->request->getProtocol());
});

test('trust x forwarded for on', function () {
    [$forwardedIp, $remoteIp] = getRemoteAddrTestPrepare(['trust_x_forwarded_for' => true]);
    self::assertEquals($forwardedIp, $this->request->getRemoteAddr());
});

test('trust x forwarded for off', function () {
    [$forwardedIp, $remoteIp] = getRemoteAddrTestPrepare(['trust_x_forwarded_for' => false]);
    self::assertEquals($remoteIp, $this->request->getRemoteAddr());
});

test('trust x forwarded for not set', function () {
    [$forwardedIp, $remoteIp] = getRemoteAddrTestPrepare([]);
    self::assertEquals($forwardedIp, $this->request->getRemoteAddr());
});

/**
 * Helper function for testTrustXForwardedFor tests that prepares the
 * environment
 *
 * @param mixed $generalConfigData Array containing overwrites for the
 * general section of the config
 */
function getRemoteAddrTestPrepare($generalConfigData = [])
{
    // Remove cached IP address from registry
    Registry::delete('remoteIpAddr');

    $_SERVER['HTTP_X_FORWARDED_FOR'] = '1.1.1.1';
    $_SERVER['REMOTE_ADDR'] = '2.2.2.2';

    $configData = & Registry::get('configData', true, []);
    $configData['general'] = $generalConfigData;

    return [$_SERVER['HTTP_X_FORWARDED_FOR'], $_SERVER['REMOTE_ADDR']];
}

test('get user var', function () {
    $_GET = [
        'par1' => '\'val1\'',
        'par2' => ' val2'
    ];
    $_POST = [
        'par3' => 'val3 ',
        'par4' => 'val4'
    ];
    self::assertEquals("'val1'", $this->request->getUserVar('par1'));
    self::assertEquals('val2', $this->request->getUserVar('par2'));
    self::assertEquals('val3', $this->request->getUserVar('par3'));
    self::assertEquals('val4', $this->request->getUserVar('par4'));
});

test('get user vars', function () {
    $_GET = [
        'par1' => '\'val1\'',
        'par2' => ' val2'
    ];
    $_POST = [
        'par3' => 'val3 ',
        'par4' => 'val4'
    ];
    $expectedResult = [
        'par1' => "'val1'",
        'par2' => 'val2',
        'par3' => 'val3',
        'par4' => 'val4'
    ];
    self::assertEquals($expectedResult, $this->request->getUserVars());
});

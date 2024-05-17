<?php

uses(\PKP\tests\classes\core\PKPRouterTestCase::class);
use PKP\core\PKPComponentRouter;
use PKP\core\Registry;
use PKP\db\DAORegistry;
use PKP\security\authorization\UserRolesRequiredPolicy;


/**
 * @see PKPTestCase::getMockedRegistryKeys()
 */
function getMockedRegistryKeys() : array
{
    return [...getMockedRegistryKeys(), 'request', 'user'];
}

beforeEach(function () {
    $this->router = new PKPComponentRouter();
});

test('supports', function () {
    $this->markTestSkipped('The method PKPRouter::testSupports() is not relevant for component routers');
});

test('supports with pathinfo successful', function () {
    $mockApplication = $this->_setUpMockEnvironment();

    $_SERVER = [
        'SCRIPT_NAME' => '/index.php',
        'PATH_INFO' => '/context1/$$$call$$$/grid/notifications/task-notifications-grid/fetch-grid'
    ];
    self::assertTrue($this->router->supports($this->request));
});

test('supports with pathinfo unsuccessful no component not enough path elements', function () {
    $mockApplication = $this->_setUpMockEnvironment();

    $_SERVER = [
        'SCRIPT_NAME' => '/index.php',
        'PATH_INFO' => '/context1/page/operation'
    ];
    self::assertEquals('', $this->router->getRequestedComponent($this->request));
    self::assertFalse($this->router->supports($this->request));
});

test('supports with pathinfo unsuccessful no component no marker', function () {
    $mockApplication = $this->_setUpMockEnvironment();

    $_SERVER = [
        'SCRIPT_NAME' => '/index.php',
        'PATH_INFO' => '/context1/path/to/handler/operation'
    ];
    self::assertEquals('', $this->router->getRequestedComponent($this->request));
    self::assertFalse($this->router->supports($this->request));
});

test('supports with pathinfo and component file does not exist', function () {
    $mockApplication = $this->_setUpMockEnvironment();

    $_SERVER = [
        'SCRIPT_NAME' => '/index.php',
        'PATH_INFO' => '/context1/$$$call$$$/inexistent/component/fetch-grid'
    ];
    self::assertEquals('inexistent.ComponentHandler', $this->router->getRequestedComponent($this->request));

    // @see PKPComponentRouter::supports() for details
    self::assertTrue($this->router->supports($this->request));
});

test('get requested component with pathinfo', function () {
    $mockApplication = $this->_setUpMockEnvironment();

    $_SERVER = [
        'SCRIPT_NAME' => '/index.php',
        'PATH_INFO' => '/context1/$$$call$$$/path/to/some-component/operation'
    ];
    self::assertEquals('path.to.SomeComponentHandler', $this->router->getRequestedComponent($this->request));
});

test('get requested component with pathinfo and malformed component string', function () {
    $mockApplication = $this->_setUpMockEnvironment();

    $_SERVER = [
        'SCRIPT_NAME' => '/index.php',
        'PATH_INFO' => '/context1/$$$call$$$/path/to/some-#component/operation'
    ];
    self::assertEquals('', $this->router->getRequestedComponent($this->request));
});

test('get requested op with pathinfo', function () {
    $mockApplication = $this->_setUpMockEnvironment();

    $_SERVER = [
        'SCRIPT_NAME' => '/index.php',
        'PATH_INFO' => '/context1/$$$call$$$/path/to/some-component/some-op'
    ];
    self::assertEquals('someOp', $this->router->getRequestedOp($this->request));
});

test('get requested op with pathinfo and malformed op string', function () {
    $mockApplication = $this->_setUpMockEnvironment();

    $_SERVER = [
        'SCRIPT_NAME' => '/index.php',
        'PATH_INFO' => '/context1/$$$call$$$/path/to/some-component/so#me-op'
    ];
    self::assertEquals('', $this->router->getRequestedOp($this->request));
});

test('route', function () {
    $this->setTestConfiguration('mysql');
    $mockApplication = $this->_setUpMockEnvironment();

    $_SERVER = [
        'SCRIPT_NAME' => '/index.php',
        'PATH_INFO' => '/context1/$$$call$$$/grid/notifications/task-notifications-grid/fetch-grid',
    ];
    $_GET = [
        'arg1' => 'val1',
        'arg2' => 'val2'
    ];

    // Simulate context DAOs
    $this->_setUpMockDAOs('context1');

    $this->expectOutputRegex('/{"status":true,"content":".*component-grid-notifications-tasknotificationsgrid/');

    // Route the request. This should call NotificationsGridHandler::fetchGrid()
    // with a reference to the request object as the first argument.
    Registry::set('request', $this->request);
    $user = new \PKP\user\User();

    /*
     * Set the id of the user here to something other than null in order for the UserRolesRequiredPolicy
     * to be able to work as it supposed to.
     * Specifically, the UserRolesRequiredPolicy::effect calls the getByUserIdGroupedByContext function
     * which needs a userId that is not nullable.
     */
    $user->setData('id', 0);
    Registry::set('user', $user);
    $serviceEndpoint = $this->router->getRpcServiceEndpoint($this->request);
    $handler = $serviceEndpoint[0];
    $handler->addPolicy(new UserRolesRequiredPolicy($this->request), true);
    $this->router->route($this->request);

    self::assertNotNull($serviceEndpoint);
    self::assertInstanceOf(\PKP\controllers\grid\notifications\NotificationsGridHandler::class, $handler);
    $firstContextDao = DAORegistry::getDAO('FirstContextDAO');
    self::assertInstanceOf('Context', $firstContextDao->getByPath('context1'));
});

test('url with pathinfo', function () {
    $this->setTestConfiguration('request1', 'classes/core/config');
    // restful URLs
    $this->_setUpMockEnvironment();
    $_SERVER = [
        'SERVER_NAME' => 'mydomain.org',
        'SCRIPT_NAME' => '/index.php',
        'PATH_INFO' => '/current-context1/$$$call$$$/current/component-class/current-op'
    ];

    // Simulate context DAOs
    $this->_setUpMockDAOs();

    $result = $this->router->url($this->request);
    self::assertEquals('http://mydomain.org/index.php/current-context1/$$$call$$$/current/component-class/current-op', $result);

    $result = $this->router->url($this->request, 'new-context1');
    self::assertEquals('http://mydomain.org/index.php/new-context1/$$$call$$$/current/component-class/current-op', $result);

    $result = $this->router->url($this->request, null, 'new.NewComponentHandler');
    self::assertEquals('http://mydomain.org/index.php/current-context1/$$$call$$$/new/new-component/current-op', $result);

    $result = $this->router->url($this->request, null, null, 'newOp');
    self::assertEquals('http://mydomain.org/index.php/current-context1/$$$call$$$/current/component-class/new-op', $result);

    $result = $this->router->url($this->request, 'new-context1', 'new.NewComponentHandler');
    self::assertEquals('http://mydomain.org/index.php/new-context1/$$$call$$$/new/new-component/current-op', $result);

    $result = $this->router->url($this->request, 'new-context1', 'new.NewComponentHandler', 'newOp');
    self::assertEquals('http://mydomain.org/index.php/new-context1/$$$call$$$/new/new-component/new-op', $result);

    $result = $this->router->url($this->request, 'new-context1', null, 'newOp');
    self::assertEquals('http://mydomain.org/index.php/new-context1/$$$call$$$/current/component-class/new-op', $result);

    $params = [
        'key1' => 'val1?',
        'key2' => ['val2-1', 'val2?2']
    ];
    $result = $this->router->url($this->request, 'new-context1', null, null, null, $params, null, true);
    self::assertEquals('http://mydomain.org/index.php/new-context1/$$$call$$$/current/component-class/current-op?key1=val1%3F&amp;key2%5B%5D=val2-1&amp;key2%5B%5D=val2%3F2', $result);

    $result = $this->router->url($this->request, 'new-context1', null, null, null, $params, null, false);
    self::assertEquals('http://mydomain.org/index.php/new-context1/$$$call$$$/current/component-class/current-op?key1=val1%3F&key2[]=val2-1&key2[]=val2%3F2', $result);

    $result = $this->router->url($this->request, 'new-context1', null, null, null, null, 'some?anchor');
    self::assertEquals('http://mydomain.org/index.php/new-context1/$$$call$$$/current/component-class/current-op#some%3Fanchor', $result);

    $result = $this->router->url($this->request, 'new-context1', null, 'newOp', null, ['key' => 'val'], 'some-anchor');
    self::assertEquals('http://mydomain.org/index.php/new-context1/$$$call$$$/current/component-class/new-op?key=val#some-anchor', $result);

    $result = $this->router->url($this->request, 'new-context1', null, null, null, ['key1' => 'val1', 'key2' => 'val2'], null, true);
    self::assertEquals('http://mydomain.org/index.php/new-context1/$$$call$$$/current/component-class/current-op?key1=val1&amp;key2=val2', $result);
});

test('url with pathinfo and overridden base url', function () {
    $this->setTestConfiguration('request1', 'classes/core/config');
    // contains overridden context
    $mockApplication = $this->_setUpMockEnvironment();
    $_SERVER = [
        'SERVER_NAME' => 'mydomain.org',
        'SCRIPT_NAME' => '/index.php',
        'PATH_INFO' => '/overridden-context/$$$call$$$/current/component-class/current-op'
    ];

    // Simulate context DAOs
    $this->_setUpMockDAOs('overridden-context');

    $result = $this->router->url($this->request);
    self::assertEquals('http://some-domain/xyz-context/$$$call$$$/current/component-class/current-op', $result);
});

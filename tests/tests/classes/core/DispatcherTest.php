<?php

uses(\PKP\tests\PKPTestCase::class);
use APP\core\Application;
use APP\core\Request;
use PHPUnit\Framework\MockObject\MockObject;
use PKP\core\Dispatcher;
use PKP\core\PKPApplication;
use PKP\core\PKPRequest;
use PKP\db\DAORegistry;

public const PATHINFO_ENABLED = true;

public const PATHINFO_DISABLED = false;

/**
 * @copydoc PKPTestCase::getMockedRegistryKeys()
 */
function getMockedRegistryKeys() : array
{
    return [...getMockedRegistryKeys(), 'application', 'dispatcher'];
}

/**
 * @see PKPTestCase::setUp()
 */
beforeEach(function () {
    // Mock application object without calling its constructor.
    /** @var Application|MockObject */
    $mockApplication = $this->getMockBuilder(Application::class)
        ->onlyMethods(['getContextName'])
        ->getMock();

    // Set up the getContextName() method
    $mockApplication->expects($this->any())
        ->method('getContextName')
        ->will($this->returnValue('firstContext'));

    $this->dispatcher = $mockApplication->getDispatcher();
    // this also adds the component router
    $this->dispatcher->addRouterName(\PKP\core\PKPPageRouter::class, 'page');

    $this->request = new Request();
});

test('url', function () {
    _setUpMockDAO();

    $baseUrl = $this->request->getBaseUrl();

    $url = $this->dispatcher->url($this->request, PKPApplication::ROUTE_PAGE, 'context1', 'somepage', 'someop');
    self::assertEquals($baseUrl . '/index.php/context1/somepage/someop', $url);

    $url = $this->dispatcher->url($this->request, PKPApplication::ROUTE_COMPONENT, 'context1', 'some.ComponentHandler', 'someOp');
    self::assertEquals($baseUrl . '/index.php/context1/$$$call$$$/some/component/some-op', $url);
});

/**
 * Create mock DAO "context1"
 * DAO will be registered with the DAORegistry.
 */
function _setUpMockDAO() : void
{
    $application = Application::get();
    $contextDao = $application->getContextDAO();
    $contextClassName = $contextDao->newDataObject()::class;
    $mockFirstContextDao = $this->getMockBuilder($contextDao::class)
        ->onlyMethods(['getByPath'])
        ->getMock();

    $contextObject = $this->getMockBuilder($contextClassName)
        ->onlyMethods(['getPath', 'getSupportedLocales'])
        ->getMock();
    $contextObject->expects($this->any())
        ->method('getPath')
        ->will($this->returnValue('context1'));
    $contextObject->expects($this->any())
        ->method('getSupportedLocales')
        ->will($this->returnValue(['en']));

    $mockFirstContextDao->expects($this->any())
        ->method('getByPath')
        ->with('context1')
        ->will($this->returnValue($contextObject));

    DAORegistry::registerDAO('FirstContextDAO', $mockFirstContextDao);
}

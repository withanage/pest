<?php

uses(\PKP\tests\PKPTestCase::class);
use APP\notification\Notification;
use PHPUnit\Framework\MockObject\MockObject;
use PKP\core\PKPRequest;
use PKP\db\DAORegistry;
use PKP\notification\NotificationDAO;
use PKP\notification\NotificationSettingsDAO;
use PKP\notification\PKPNotification;
use PKP\notification\PKPNotificationManager;

private const NOTIFICATION_ID = 1;

/**
 * @see PKPTestCase::getMockedRegistryKeys()
 */
function getMockedRegistryKeys() : array
{
    return [...getMockedRegistryKeys(), 'request', 'application'];
}

/**
 * @see PKPTestCase::getMockedContainerKeys()
 */
function getMockedContainerKeys() : array
{
    return [...getMockedContainerKeys(), \PKP\user\DAO::class];
}

test('get notification message', function () {
    $notification = getTrivialNotification();
    $notification->setType(PKPNotification::NOTIFICATION_TYPE_REVIEW_ASSIGNMENT);

    $requestDummy = $this->getMockBuilder(PKPRequest::class)->getMock();
    $result = $this->notificationMgr->getNotificationMessage($requestDummy, $notification);

    expect($result)->toEqual(__('notification.type.reviewAssignment'));
});

test('create notification', function ($notification, $notificationParams = []) {
    $notificationMgrStub = getMgrStubForCreateNotificationTests();
    injectNotificationDaoMock($notification);

    if (!empty($notificationParams)) {
        injectNotificationSettingsDaoMock($notificationParams);
    }

    $result = exerciseCreateNotification($notificationMgrStub, $notification, $notificationParams);

    expect($result)->toEqual($notification);
})->with('trivialNotificationDataProvider');

test('create notification blocked', function () {
    $trivialNotification = getTrivialNotification();

    $blockedNotificationTypes = [$trivialNotification->getType()];
    $notificationMgrStub = getMgrStubForCreateNotificationTests($blockedNotificationTypes);

    $result = exerciseCreateNotification($notificationMgrStub, $trivialNotification);

    expect($result)->toEqual(null);
});

test('create trivial notification', function ($notification, $notificationParams = []) {
    $trivialNotification = $notification;

    // Adapt the notification to the expected result.
    $trivialNotification->unsetData('assocId');
    $trivialNotification->unsetData('assocType');
    $trivialNotification->setType(PKPNotification::NOTIFICATION_TYPE_SUCCESS);

    injectNotificationDaoMock($trivialNotification);
    if (!empty($notificationParams)) {
        injectNotificationSettingsDaoMock($notificationParams);
    }

    $result = $this->notificationMgr->createTrivialNotification($trivialNotification->getUserId());

    expect($result)->toEqual($trivialNotification);
})->with('trivialNotificationDataProvider');

/**
 * Provides data to be used by tests that expects two cases:
 * 1 - a trivial notification
 * 2 - a trivial notification and its parameters.
 *
 * @return array
 */
dataset('trivialNotificationDataProvider', function () {
    $trivialNotification = getTrivialNotification();
    $notificationParams = ['param1' => 'param1Value'];
    return [
        'Notification without params' => [$trivialNotification],
        'Notification with params' => [$trivialNotification, $notificationParams]
    ];
});

//
// Protected methods.
//
/**
 * @see PKPTestCase::getMockedDAOs()
 */
function getMockedDAOs() : array
{
    return [...getMockedDAOs(), 'NotificationDAO', 'NotificationSettingsDAO'];
}

beforeEach(function () {
    $this->notificationMgr = new PKPNotificationManager();
});

//
// Helper methods.
//
/**
 * Exercise the system for all test methods that covers the
 * PKPNotificationManager::createNotification() method.
 *
 * @param PKPNotificationManager $notificationMgr An instance of the
 * notification manager.
 * @param PKPNotification $notificationToCreate
 * @param array $notificationToCreateParams
 * @param mixed $request (optional)
 */
function exerciseCreateNotification($notificationMgr, $notificationToCreate, $notificationToCreateParams = [], $request = null)
{
    if (is_null($request)) {
        $request = $this->getMockBuilder(PKPRequest::class)->getMock();
    }

    return $notificationMgr->createNotification(
        $request,
        $notificationToCreate->getUserId(),
        $notificationToCreate->getType(),
        $notificationToCreate->getContextId(),
        $notificationToCreate->getAssocType(),
        $notificationToCreate->getAssocId(),
        $notificationToCreate->getLevel(),
        $notificationToCreateParams
    );
}

/**
 * Get the notification manager stub for tests that
 * covers the PKPNotificationManager::createNotification() method.
 *
 * @param array $blockedNotifications (optional) Each notification type
 * that is blocked by user. Will be used as return value for the
 * getUserBlockedNotifications method.
 *
 * @return MockObject|PKPNotificationManager
 */
function getMgrStubForCreateNotificationTests($blockedNotifications = [])
{
    $notificationMgrStub = $this->getMockBuilder(PKPNotificationManager::class)
        ->onlyMethods(['getUserBlockedNotifications', 'getNotificationUrl'])
        ->getMock();

    $notificationMgrStub->expects($this->any())
        ->method('getUserBlockedNotifications')
        ->will($this->returnValue($blockedNotifications));

    $notificationMgrStub->expects($this->any())
        ->method('getNotificationUrl')
        ->will($this->returnValue('anyNotificationUrl'));

    return $notificationMgrStub;
}

/**
 * Setup NotificationDAO mock and register it.
 *
 * @param PKPNotification $notification A notification that is
 * expected to be inserted by the DAO.
 */
function injectNotificationDaoMock($notification)
{
    $notificationDaoMock = $this->getMockBuilder(NotificationDAO::class)
        ->onlyMethods(['insertObject'])
        ->getMock();
    $notificationDaoMock->expects($this->once())
        ->method('insertObject')
        ->with($this->equalTo($notification))
        ->will($this->returnValue(self::NOTIFICATION_ID));

    DAORegistry::registerDAO('NotificationDAO', $notificationDaoMock);
}

/**
 * Setup NotificationSettingsDAO mock and register it.
 *
 * @param array $notificationParams Notification parameters.
 */
function injectNotificationSettingsDaoMock($notificationParams)
{
    // Mock NotificationSettingsDAO.
    $notificationSettingsDaoMock = $this->getMockBuilder(NotificationSettingsDAO::class)->getMock();
    $notificationSettingsDaoMock->expects($this->any())
        ->method('updateNotificationSetting')
        ->with(
            $this->equalTo(self::NOTIFICATION_ID),
            $this->equalTo(key($notificationParams)),
            $this->equalTo(current($notificationParams))
        );

    // Inject notification settings DAO mock.
    DAORegistry::registerDAO('NotificationSettingsDAO', $notificationSettingsDaoMock);
}

/**
 * Get a trivial notification filled with test data.
 *
 * @return PKPNotification
 */
function getTrivialNotification()
{
    /** @var NotificationDAO */
    $notificationDao = DAORegistry::getDAO('NotificationDAO');
    $notification = $notificationDao->newDataObject();
    $anyTestInteger = 1;
    $notification->setUserId($anyTestInteger);
    $notification->setType($anyTestInteger);
    $notification->setContextId(\PKP\core\PKPApplication::CONTEXT_ID_NONE);
    $notification->setAssocType($anyTestInteger);
    $notification->setAssocId($anyTestInteger);
    $notification->setLevel(Notification::NOTIFICATION_LEVEL_TRIVIAL);

    return $notification;
}

<?php

uses(\PKP\tests\PKPTestCase::class);
use PKP\config\Config;
use PKP\mail\Mail;
use PKP\mail\Mailable;
use PKP\scheduledTask\ScheduledTaskHelper;

test('notify execution result error', function ($taskId, $taskName, $message) {
    $taskResult = false;
    $expectedSubject = __(ScheduledTaskHelper::SCHEDULED_TASK_MESSAGE_TYPE_ERROR);
    _setReportErrorOnly('On');
    $expectedTestResult = null;

    // Will send email (it's null because we mocked Mail::send()).
    $helper = _getHelper($expectedSubject, $message);

    // Exercise the system.
    $actualResult = $helper->notifyExecutionResult($taskId, $taskName, $taskResult, $message);
    expect($actualResult)->toEqual($expectedTestResult);

    // Now set report error only to off and we should get the same result.
    _setReportErrorOnly('Off');
    $actualResult = $helper->notifyExecutionResult($taskId, $taskName, $taskResult, $message);
    expect($actualResult)->toEqual($expectedTestResult);
})->with('notifyExecutionResultTestsDataProvider');

test('notify execution result success', function ($taskId, $taskName, $message) {
    $taskResult = true;
    $expectedSubject = __(ScheduledTaskHelper::SCHEDULED_TASK_MESSAGE_TYPE_COMPLETED);
    _setReportErrorOnly('On');
    $expectedTestResult = false;

    // Will NOT send email.
    $helper = _getHelper($expectedSubject, $message);

    // Exercise the system.
    $actualResult = $helper->notifyExecutionResult($taskId, $taskName, $taskResult, $message);
    expect($actualResult)->toEqual($expectedTestResult);

    // Now change the report setting, so success emails will also be sent.
    _setReportErrorOnly('Off');
    $expectedTestResult = null;
    // Will send email.
    $actualResult = $helper->notifyExecutionResult($taskId, $taskName, $taskResult, $message);
    expect($actualResult)->toEqual($expectedTestResult);
})->with('notifyExecutionResultTestsDataProvider');

/**
 * All notifyExecutionResult tests data provider.
 *
 * @return array
 */
dataset('notifyExecutionResultTestsDataProvider', function () {
    return [['someTaskId', 'TaskName', 'Any message']];
});

//
// Private helper methods.
//
/**
 * Get helper mock object to exercise the system.
 *
 * @param string $expectedSubject
 * @param string $message
 *
 * @return ScheduledTaskHelper
 */
function _getHelper($expectedSubject, $message)
{
    $helperMock = $this->getMockBuilder(ScheduledTaskHelper::class)
        ->onlyMethods(['getMessage'])
        ->setConstructorArgs(['some@email.com', 'Contact name'])
        ->getMock();
    $helperMock->expects($this->any())
        ->method('getMessage')
        ->will($this->returnValue($message));

    // Helper will use the Mail::send() method. Mock it.
    $mailMock = $this->getMockBuilder(Mailable::class)
        ->onlyMethods(['send', 'body', 'subject'])
        ->getMock();

    $mailMock->expects($this->any())
        ->method('send');

    $mailMock->expects($this->any())
        ->method('body')
        ->with($this->equalTo($message));

    $mailMock->expects($this->any())
        ->method('subject')
        ->with($this->stringContains($expectedSubject));

    return $helperMock;
}

/**
 * Set the scheduled_task_report_error_only setting value.
 *
 * @param string $state 'On' or 'Off'
 */
function _setReportErrorOnly($state)
{
    $configData = & Config::getData();
    $configData['general']['scheduled_tasks_report_error_only'] = $state;
}

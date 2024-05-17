<?php

uses(\PKP\tests\PKPTestCase::class);
use PKP\core\JSONMessage;

test('get string', function () {
    // Create a test object.
    $testObject = new stdClass();
    $testObject->someInt = 5;
    $testObject->someFloat = 5.5;
    $json = new JSONMessage(
        $status = true,
        $content = 'test content',
        $elementId = '0',
        $additionalAttributes = ['testObj' => $testObject]
    );
    $json->setEvent('someEvent', ['eventDataKey' => ['item1', 'item2']]);

    // Render the JSON message.
    $expectedString = '{"status":true,"content":"test content",' .
        '"elementId":"0","events":[{"name":"someEvent","data":{"eventDataKey":["item1","item2"]}}],' .
        '"testObj":{"someInt":5,"someFloat":5.5}}';
    self::assertEquals($expectedString, $json->getString());
});

<?php

uses(\PKP\tests\PKPTestCase::class);
use APP\core\Application;
use PKP\core\Registry;
use PKP\form\Form;
use PKP\form\validation\FormValidatorPost;

/**
 * @see PKPTestCase::getMockedRegistryKeys()
 */
function getMockedRegistryKeys() : array
{
    return [...getMockedRegistryKeys(), 'request'];
}

beforeEach(function () {
    $request = Application::get()->getRequest();
    $mock = Mockery::mock($request)
        // Custom isPost()
        ->shouldReceive('isPost')->andReturnUsing(fn () => $this->_isPosted)
        ->getMock();

    // Replace the request singleton by a mock
    Registry::set('request', $mock);
});

test('is valid', function () {
    // Instantiate test validator
    $form = new Form('some template');
    $validator = new FormValidatorPost($form, 'some.message.key');

    $this->_isPosted = true;
    self::assertTrue($validator->isValid());

    $this->_isPosted = false;
    self::assertFalse($validator->isValid());
});

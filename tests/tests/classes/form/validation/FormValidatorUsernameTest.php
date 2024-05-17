<?php

uses(\PKP\tests\PKPTestCase::class);
use PKP\form\Form;
use PKP\form\validation\FormValidator;
use PKP\form\validation\FormValidatorUsername;

test('is valid', function () {
    $form = new Form('some template');

    // Allowed characters are a-z, 0-9, -, _. The characters - and _ are
    // not allowed at the start of the string.
    $form->setData('testData', 'a-z0123_bkj');
    $validator = new FormValidatorUsername($form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key');
    self::assertTrue($validator->isValid());

    // Test invalid strings
    $form->setData('testData', '-z0123_bkj');
    $validator = new FormValidatorUsername($form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key');
    self::assertFalse($validator->isValid());

    $form->setData('testData', 'abc#def');
    $validator = new FormValidatorUsername($form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key');
    self::assertFalse($validator->isValid());
});

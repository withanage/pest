<?php

uses(\PKP\tests\PKPTestCase::class);
use PKP\form\Form;
use PKP\form\validation\FormValidator;
use PKP\form\validation\FormValidatorRegExp;

test('is valid', function () {
    $form = new Form('some template');
    $form->setData('testData', 'some data');

    $validator = new FormValidatorRegExp($form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key', '/some.*/');
    self::assertTrue($validator->isValid());

    $validator = new FormValidatorRegExp($form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key', '/some more.*/');
    self::assertFalse($validator->isValid());
});

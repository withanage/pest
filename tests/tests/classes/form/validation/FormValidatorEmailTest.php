<?php

uses(\PKP\tests\PKPTestCase::class);
use PKP\form\Form;
use PKP\form\validation\FormValidator;
use PKP\form\validation\FormValidatorEmail;

test('is valid', function () {
    $form = new Form('some template');

    $form->setData('testData', 'some.address@gmail.com');
    $validator = new FormValidatorEmail($form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key');
    self::assertTrue($validator->isValid());
    self::assertEquals(['testData' => ['required', 'email']], $form->cssValidation);

    $form->setData('testData', 'anything else');
    $validator = new FormValidatorEmail($form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key');
    self::assertFalse($validator->isValid());
});

<?php

uses(\PKP\tests\PKPTestCase::class);
use PKP\form\Form;
use PKP\form\validation\FormValidator;
use PKP\form\validation\FormValidatorLocaleEmail;

test('is valid', function () {
    $form = new Form('some template');

    $form->setData('testData', ['en' => 'some.address@gmail.com']);
    $validator = new FormValidatorLocaleEmail($form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key');
    self::assertTrue($validator->isValid());

    $form->setData('testData', 'some.address@gmail.com');
    $validator = new FormValidatorLocaleEmail($form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key');
    self::assertFalse($validator->isValid());

    $form->setData('testData', ['en' => 'anything else']);
    $validator = new FormValidatorLocaleEmail($form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key');
    self::assertFalse($validator->isValid());
});

<?php

uses(\PKP\tests\PKPTestCase::class);
use PKP\form\Form;
use PKP\form\validation\FormValidator;
use PKP\form\validation\FormValidatorInSet;

test('is valid', function () {
    $form = new Form('some template');

    // Instantiate test validator
    $acceptedValues = ['val1', 'val2'];
    $validator = new FormValidatorInSet($form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key', $acceptedValues);

    $form->setData('testData', 'val1');
    self::assertTrue($validator->isValid());

    $form->setData('testData', 'anything else');
    self::assertFalse($validator->isValid());
});

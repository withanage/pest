<?php

uses(\PKP\tests\PKPTestCase::class);
use PKP\form\Form;
use PKP\form\validation\FormValidatorBoolean;

test('is valid', function () {
    $form = new Form('some template');

    // Instantiate test validator
    $validator = new FormValidatorBoolean($form, 'testData', 'some.message.key');

    $form->setData('testData', '');
    self::assertTrue($validator->isValid());

    $form->setData('testData', 'on');
    self::assertTrue($validator->isValid());

    $form->setData('testData', true);
    self::assertTrue($validator->isValid());

    $form->setData('testData', false);
    self::assertTrue($validator->isValid());

    $form->setData('testData', 'anything else');
    self::assertFalse($validator->isValid());
});

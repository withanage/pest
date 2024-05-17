<?php

uses(\PKP\tests\PKPTestCase::class);
use PKP\form\Form;
use PKP\form\validation\FormValidator;
use PKP\form\validation\FormValidatorUrl;

test('is valid', function () {
    $form = new Form('some template');

    // test valid urls
    $form->setData('testUrl', 'http://some.domain.org/some/path?some=query#fragment');
    $validator = new FormValidatorUrl($form, 'testUrl', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key');
    self::assertTrue($validator->isValid());
    self::assertEquals(['testUrl' => ['required', 'url']], $form->cssValidation);

    $form->setData('testUrl', 'http://192.168.0.1/');
    $validator = new FormValidatorUrl($form, 'testUrl', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key');
    self::assertTrue($validator->isValid());

    // test invalid urls
    $form->setData('testUrl', 'http//missing-colon.org');
    $validator = new FormValidatorUrl($form, 'testUrl', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key');
    self::assertFalse($validator->isValid());

    $form->setData('testUrl', 'http:/missing-slash.org');
    $validator = new FormValidatorUrl($form, 'testUrl', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key');
    self::assertFalse($validator->isValid());
});

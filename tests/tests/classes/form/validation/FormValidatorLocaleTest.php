<?php

uses(\PKP\tests\PKPTestCase::class);
use PKP\form\Form;
use PKP\form\validation\FormValidator;
use PKP\form\validation\FormValidatorLocale;

test('get message', function () {
    $form = new Form('some template');
    $formValidator = new FormValidatorLocale($form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key');
    self::assertSame('##some.message.key## (English)', $formValidator->getMessage());
});

test('get field value', function () {
    $form = new Form('some template');
    $formValidator = new FormValidatorLocale($form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key');
    self::assertSame('', $formValidator->getFieldValue());

    $form->setData('testData', null);
    $formValidator = new FormValidatorLocale($form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key');
    self::assertSame('', $formValidator->getFieldValue());

    $form->setData('testData', ['en' => null]);
    $formValidator = new FormValidatorLocale($form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key');
    self::assertSame('', $formValidator->getFieldValue());

    $form->setData('testData', ['en' => 0]);
    $formValidator = new FormValidatorLocale($form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key');
    self::assertSame('0', $formValidator->getFieldValue());

    $form->setData('testData', ['en' => '0']);
    $formValidator = new FormValidatorLocale($form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key');
    self::assertSame('0', $formValidator->getFieldValue());

    $form->setData('testData', ' some text ');
    $formValidator = new FormValidatorLocale($form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key');
    self::assertSame('', $formValidator->getFieldValue());

    $form->setData('testData', ['de' => ' some text ']);
    $formValidator = new FormValidatorLocale($form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key');
    self::assertSame('', $formValidator->getFieldValue());

    $form->setData('testData', ['en' => ' some text ']);
    $formValidator = new FormValidatorLocale($form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key');
    self::assertSame('some text', $formValidator->getFieldValue());

    $form->setData('testData', ['en' => [' some text ']]);
    $formValidator = new FormValidatorLocale($form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key');
    self::assertSame([' some text '], $formValidator->getFieldValue());
});

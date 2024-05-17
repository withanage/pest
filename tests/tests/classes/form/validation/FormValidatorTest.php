<?php

uses(\PKP\tests\PKPTestCase::class);
use PKP\form\Form;
use PKP\form\validation\FormValidator;
use PKP\validation\ValidatorUrl;


beforeEach(function () {
    $this->form = new Form('some template');
});

test('constructor', function () {
    // Instantiate a test validator
    $validator = new ValidatorUrl();

    // Test CSS validation flags
    $formValidator = new FormValidator($this->form, 'testData', FormValidator::FORM_VALIDATOR_OPTIONAL_VALUE, 'some.message.key');
    self::assertEquals(['testData' => []], $this->form->cssValidation);
    self::assertSame(FormValidator::FORM_VALIDATOR_OPTIONAL_VALUE, $formValidator->getType());

    $formValidator = new FormValidator($this->form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key', $validator);
    self::assertEquals(['testData' => ['required']], $this->form->cssValidation);
    self::assertSame(FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, $formValidator->getType());

    // Test getters
    self::assertSame('testData', $formValidator->getField());
    self::assertSame($this->form, $formValidator->getForm());
    self::assertSame($validator, $formValidator->getValidator());
});

test('get message', function () {
    $formValidator = new FormValidator($this->form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key');
    self::assertSame('##some.message.key##', $formValidator->getMessage());
});

test('get field value', function () {
    $formValidator = new FormValidator($this->form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key');
    self::assertSame('', $formValidator->getFieldValue());

    $this->form->setData('testData', null);
    $formValidator = new FormValidator($this->form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key');
    self::assertSame('', $formValidator->getFieldValue());

    $this->form->setData('testData', 0);
    $formValidator = new FormValidator($this->form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key');
    self::assertSame('0', $formValidator->getFieldValue());

    $this->form->setData('testData', '0');
    $formValidator = new FormValidator($this->form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key');
    self::assertSame('0', $formValidator->getFieldValue());

    $this->form->setData('testData', ' some text ');
    $formValidator = new FormValidator($this->form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key');
    self::assertSame('some text', $formValidator->getFieldValue());

    $this->form->setData('testData', [' some text ']);
    $formValidator = new FormValidator($this->form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key');
    self::assertSame([' some text '], $formValidator->getFieldValue());
});

test('is empty and optional', function () {
    // When the validation type is "required" then the method should return
    // false even if the given data field is empty.
    $this->form->setData('testData', '');
    $formValidator = new FormValidator($this->form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key');
    self::assertFalse($formValidator->isEmptyAndOptional());

    // If the validation type is "optional" but the given data field is not empty
    // then the method should also return false.
    $this->form->setData('testData', 'something');
    $formValidator = new FormValidator($this->form, 'testData', FormValidator::FORM_VALIDATOR_OPTIONAL_VALUE, 'some.message.key');
    self::assertFalse($formValidator->isEmptyAndOptional());

    $this->form->setData('testData', ['something']);
    $formValidator = new FormValidator($this->form, 'testData', FormValidator::FORM_VALIDATOR_OPTIONAL_VALUE, 'some.message.key');
    self::assertFalse($formValidator->isEmptyAndOptional());

    // When the validation type is "optional" and the value empty then return true
    $this->form->setData('testData', '');
    $formValidator = new FormValidator($this->form, 'testData', FormValidator::FORM_VALIDATOR_OPTIONAL_VALUE, 'some.message.key');
    self::assertTrue($formValidator->isEmptyAndOptional());

    // Test border conditions
    $this->form->setData('testData', null);
    $formValidator = new FormValidator($this->form, 'testData', FormValidator::FORM_VALIDATOR_OPTIONAL_VALUE, 'some.message.key');
    self::assertTrue($formValidator->isEmptyAndOptional());

    $this->form->setData('testData', 0);
    $formValidator = new FormValidator($this->form, 'testData', FormValidator::FORM_VALIDATOR_OPTIONAL_VALUE, 'some.message.key');
    self::assertFalse($formValidator->isEmptyAndOptional());

    $this->form->setData('testData', '0');
    $formValidator = new FormValidator($this->form, 'testData', FormValidator::FORM_VALIDATOR_OPTIONAL_VALUE, 'some.message.key');
    self::assertFalse($formValidator->isEmptyAndOptional());

    $this->form->setData('testData', []);
    $formValidator = new FormValidator($this->form, 'testData', FormValidator::FORM_VALIDATOR_OPTIONAL_VALUE, 'some.message.key');
    self::assertTrue($formValidator->isEmptyAndOptional());
});

test('is valid', function () {
    // We don't need to test the case where a validator is set, this
    // is sufficiently tested by the other FormValidator* tests.
    // Test default validation (without internal validator set and optional values)
    $formValidator = new FormValidator($this->form, 'testData', FormValidator::FORM_VALIDATOR_OPTIONAL_VALUE, 'some.message.key');
    self::assertTrue($formValidator->isValid());

    // Test default validation (without internal validator set and required values)
    $formValidator = new FormValidator($this->form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key');
    self::assertFalse($formValidator->isValid());

    $this->form->setData('testData', []);
    $formValidator = new FormValidator($this->form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key');
    self::assertFalse($formValidator->isValid());

    $this->form->setData('testData', 'some value');
    $formValidator = new FormValidator($this->form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key');
    self::assertTrue($formValidator->isValid());

    $this->form->setData('testData', ['some value']);
    $formValidator = new FormValidator($this->form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key');
    self::assertTrue($formValidator->isValid());
});

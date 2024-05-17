<?php

uses(\PKP\tests\PKPTestCase::class);
use PKP\form\Form;
use PKP\form\validation\FormValidator;
use PKP\form\validation\FormValidatorCustom;

test('is valid', function () {
    $form = new Form('some template');
    $validationFunction = userValidationFunction(...);

    // Tests are completely bypassed when the validation type is
    // "optional" and the test field is empty. We make sure this is the
    // case by returning 'false' for the custom validation function.
    $form->setData('testData', '');
    $validator = new FormValidatorCustom($form, 'testData', FormValidator::FORM_VALIDATOR_OPTIONAL_VALUE, 'some.message.key', $validationFunction, [false]);
    self::assertTrue($validator->isValid());
    self::assertSame(null, $this->checkedValue);

    // Simulate valid data
    $form->setData('testData', 'xyz');
    $validator = new FormValidatorCustom($form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key', $validationFunction, [true]);
    self::assertTrue($validator->isValid());
    self::assertSame('xyz', $this->checkedValue);

    // Simulate invalid data
    $form->setData('testData', 'xyz');
    $validator = new FormValidatorCustom($form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key', $validationFunction, [false]);
    self::assertFalse($validator->isValid());
    self::assertSame('xyz', $this->checkedValue);

    // Simulate valid data with negation of the user function return value
    $form->setData('testData', 'xyz');
    $validator = new FormValidatorCustom($form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key', $validationFunction, [false], true);
    self::assertTrue($validator->isValid());
    self::assertSame('xyz', $this->checkedValue);
});

/**
 * This function is used as a custom validation callback for
 * fields.
 * It simply reflects the additional argument so that we can
 * easily manipulate its return value. The value passed in
 * to this method is saved internally for later inspection.
 *
 * @param string $value
 * @param bool $additionalArgument
 *
 * @return bool the value passed in to $additionalArgument
 */
function userValidationFunction($value, $additionalArgument)
{
    $this->checkedValue = $value;
    return $additionalArgument;
}

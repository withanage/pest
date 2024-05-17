<?php

uses(\PKP\tests\PKPTestCase::class);
use PKP\form\Form;
use PKP\form\validation\FormValidator;
use PKP\form\validation\FormValidatorArrayCustom;

beforeEach(function () {
    $this->form = new Form('some template');
    $this->subfieldValidation = userFunctionForSubfields(...);
    $this->localeFieldValidation = userFunctionForLocaleFields(...);
});

test('is valid optional and empty', function () {
    // Tests are completely bypassed when the validation type is
    // "optional" and the test data are empty. We make sure this is the
    // case by always returning 'false' for the custom validation function.
    $this->form->setData('testData', '');
    $validator = new FormValidatorArrayCustom($this->form, 'testData', FormValidator::FORM_VALIDATOR_OPTIONAL_VALUE, 'some.message.key', $this->subfieldValidation, [false]);
    self::assertTrue($validator->isValid());
    self::assertEquals([], $validator->getErrorFields());
    self::assertSame([], $this->checkedValues);

    $this->form->setData('testData', []);
    $validator = new FormValidatorArrayCustom($this->form, 'testData', FormValidator::FORM_VALIDATOR_OPTIONAL_VALUE, 'some.message.key', $this->subfieldValidation, [false]);
    self::assertTrue($validator->isValid());
    self::assertEquals([], $validator->getErrorFields());
    self::assertSame([], $this->checkedValues);

    // The data are valid when they contain only empty (sub-)sub-fields and the validation type is "optional".
    $this->form->setData('testData', ['subfield1' => [], 'subfield2' => '']);
    $validator = new FormValidatorArrayCustom($this->form, 'testData', FormValidator::FORM_VALIDATOR_OPTIONAL_VALUE, 'some.message.key', $this->subfieldValidation, [false]);
    self::assertTrue($validator->isValid());
    self::assertEquals([], $validator->getErrorFields());
    self::assertSame([], $this->checkedValues);

    $this->form->setData('testData', ['subfield1' => ['subsubfield1' => [], 'subsubfield2' => '']]);
    $validator = new FormValidatorArrayCustom($this->form, 'testData', FormValidator::FORM_VALIDATOR_OPTIONAL_VALUE, 'some.message.key', $this->subfieldValidation, [false], false, ['subsubfield1', 'subsubfield2']);
    self::assertTrue($validator->isValid());
    self::assertEquals([], $validator->getErrorFields());
    self::assertSame([], $this->checkedValues);
});

test('is valid no array', function () {
    // Field data must be an array, otherwise validation fails
    $this->form->setData('testData', '');
    $validator = new FormValidatorArrayCustom($this->form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key', $this->subfieldValidation, [true]);
    self::assertFalse($validator->isValid());
    self::assertEquals([], $validator->getErrorFields());
    self::assertSame([], $this->checkedValues);
});

test('is valid check all subfields', function () {
    // Check non-locale data
    $this->form->setData('testData', ['subfield1' => 'abc', 'subfield2' => '0']);
    $validator = new FormValidatorArrayCustom($this->form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key', $this->subfieldValidation, [true]);
    self::assertTrue($validator->isValid());
    self::assertEquals([], $validator->getErrorFields());
    self::assertSame(['abc', '0'], $this->checkedValues);
    $this->checkedValues = [];

    // Check complement return
    $validator = new FormValidatorArrayCustom($this->form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key', $this->subfieldValidation, [false], true);
    self::assertTrue($validator->isValid());
    self::assertEquals([], $validator->getErrorFields());
    self::assertSame(['abc', '0'], $this->checkedValues);
    $this->checkedValues = [];

    // Simulate invalid data (check function returns false)
    $validator = new FormValidatorArrayCustom($this->form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key', $this->subfieldValidation, [false]);
    self::assertFalse($validator->isValid());
    self::assertEquals(['testData[subfield1]', 'testData[subfield2]'], $validator->getErrorFields());
    self::assertSame(['abc', '0'], $this->checkedValues);
    $this->checkedValues = [];

    // Check locale data
    $this->form->setData('testData', ['en' => 'abc', 'de' => 'def']);
    $validator = new FormValidatorArrayCustom($this->form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key', $this->localeFieldValidation, [true], false, [], true);
    self::assertTrue($validator->isValid());
    self::assertEquals([], $validator->getErrorFields());
    self::assertSame(['en' => ['abc'], 'de' => ['def']], $this->checkedValues);
    $this->checkedValues = [];

    // Simulate invalid locale data
    $validator = new FormValidatorArrayCustom($this->form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key', $this->localeFieldValidation, [false], false, [], true);
    self::assertFalse($validator->isValid());
    self::assertEquals(['en' => 'testData[en]', 'de' => 'testData[de]'], $validator->getErrorFields());
    self::assertSame(['en' => ['abc'], 'de' => ['def']], $this->checkedValues);
    $this->checkedValues = [];
});

test('is valid check explicit subsubfields', function () {
    // Check non-locale data
    $testArray = [
        'subfield1' => ['subsubfield1' => 'abc', 'subsubfield2' => 'def'],
        'subfield2' => ['subsubfield1' => '0', 'subsubfield2' => 0] // also test allowed boarder conditions
    ];
    $this->form->setData('testData', $testArray);
    $validator = new FormValidatorArrayCustom($this->form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key', $this->subfieldValidation, [true], false, ['subsubfield1', 'subsubfield2']);
    self::assertTrue($validator->isValid());
    self::assertEquals([], $validator->getErrorFields());
    self::assertSame(['abc', 'def', '0', 0], $this->checkedValues);
    $this->checkedValues = [];

    // Check complement return
    $validator = new FormValidatorArrayCustom($this->form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key', $this->subfieldValidation, [false], true, ['subsubfield1', 'subsubfield2']);
    self::assertTrue($validator->isValid());
    self::assertEquals([], $validator->getErrorFields());
    self::assertSame(['abc', 'def', '0', 0], $this->checkedValues);
    $this->checkedValues = [];

    // Simulate invalid data (check function returns false)
    $validator = new FormValidatorArrayCustom($this->form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key', $this->subfieldValidation, [false], false, ['subsubfield1', 'subsubfield2']);
    self::assertFalse($validator->isValid());
    $expectedErrors = [
        'testData[subfield1][subsubfield1]', 'testData[subfield1][subsubfield2]',
        'testData[subfield2][subsubfield1]', 'testData[subfield2][subsubfield2]'
    ];
    self::assertEquals($expectedErrors, $validator->getErrorFields());
    self::assertSame(['abc', 'def', '0', 0], $this->checkedValues);
    $this->checkedValues = [];

    // Check locale data
    $testArray = [
        'en' => ['subsubfield1' => 'abc', 'subsubfield2' => 'def'],
        'de' => ['subsubfield1' => 'uvw', 'subsubfield2' => 'xyz']
    ];
    $this->form->setData('testData', $testArray);
    $validator = new FormValidatorArrayCustom($this->form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key', $this->localeFieldValidation, [true], false, ['subsubfield1', 'subsubfield2'], true);
    self::assertTrue($validator->isValid());
    self::assertEquals([], $validator->getErrorFields());
    self::assertSame(['en' => ['abc', 'def'], 'de' => ['uvw', 'xyz']], $this->checkedValues);
    $this->checkedValues = [];

    // Simulate invalid locale data
    $validator = new FormValidatorArrayCustom($this->form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key', $this->localeFieldValidation, [false], false, ['subsubfield1', 'subsubfield2'], true);
    self::assertFalse($validator->isValid());
    $expectedErrors = [
        'en' => [
            'testData[en][subsubfield1]', 'testData[en][subsubfield2]'
        ],
        'de' => [
            'testData[de][subsubfield1]', 'testData[de][subsubfield2]'
        ]
    ];
    self::assertEquals($expectedErrors, $validator->getErrorFields());
    self::assertSame(['en' => ['abc', 'def'], 'de' => ['uvw', 'xyz']], $this->checkedValues);
    $this->checkedValues = [];
});

test('is valid with border conditions', function () {
    // Make sure that we get 'null' in the user function
    // whenever an expected field doesn't exist in the value array.
    $testArray = [
        'subfield1' => ['subsubfield1' => null, 'subsubfield2' => ''],
        'subfield2' => ['subsubfield2' => 0]
    ];
    $this->form->setData('testData', $testArray);
    $validator = new FormValidatorArrayCustom($this->form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key', $this->subfieldValidation, [true], false, ['subsubfield1', 'subsubfield2']);
    self::assertTrue($validator->isValid());
    self::assertEquals([], $validator->getErrorFields());
    self::assertSame([null, '', null, 0], $this->checkedValues);
    $this->checkedValues = [];

    // Pass in a one-dimensional array where a two-dimensional array is expected
    $testArray = ['subfield1' => 'abc', 'subfield2' => 'def'];
    $this->form->setData('testData', $testArray);
    $validator = new FormValidatorArrayCustom($this->form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key', $this->subfieldValidation, [true], false, ['subsubfield']);
    self::assertFalse($validator->isValid());
    self::assertEquals(['testData[subfield1]', 'testData[subfield2]'], $validator->getErrorFields());
    self::assertSame([], $this->checkedValues);
    $this->checkedValues = [];

    // Pass in a one-dimensional locale array where a two-dimensional array is expected
    $testArray = ['en' => 'abc', 'de' => 'def'];
    $this->form->setData('testData', $testArray);
    $validator = new FormValidatorArrayCustom($this->form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key', $this->localeFieldValidation, [true], false, ['subsubfield'], true);
    self::assertFalse($validator->isValid());
    self::assertEquals(['en' => 'testData[en]', 'de' => 'testData[de]'], $validator->getErrorFields());
    self::assertSame([], $this->checkedValues);
    $this->checkedValues = [];
});

test('is array', function () {
    $this->form->setData('testData', ['subfield' => 'abc']);
    $validator = new FormValidatorArrayCustom($this->form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key', $this->subfieldValidation);
    self::assertTrue($validator->isArray());

    $this->form->setData('testData', 'field');
    self::assertFalse($validator->isArray());
});

/**
 * This function is used as a custom validation callback for
 * one-dimensional data fields.
 * It simply reflects the additional argument so that we can
 * easily manipulate its return value. The values passed in
 * to this method are saved internally for later inspection.
 *
 * @param string $value
 * @param bool $additionalArgument
 *
 * @return bool the value passed in to $additionalArgument
 */
function userFunctionForSubfields($value, $additionalArgument)
{
    $this->checkedValues[] = $value;
    return $additionalArgument;
}

/**
 * This function is used as a custom validation callback for
 * two-dimensional data fields.
 * It simply reflects the additional argument so that we can
 * easily manipulate its return value. The keys and values
 * passed in to this method are saved internally for later
 * inspection.
 *
 * @param string $value
 * @param string $key
 * @param bool $additionalArgument
 *
 * @return bool the value passed in to $additionalArgument
 */
function userFunctionForLocaleFields($value, $key, $additionalArgument)
{
    if (!isset($this->checkedValues[$key])) {
        $this->checkedValues[$key] = [];
    }
    $this->checkedValues[$key][] = $value;
    return $additionalArgument;
}

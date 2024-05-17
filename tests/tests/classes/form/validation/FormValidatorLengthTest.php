<?php

uses(\PKP\tests\PKPTestCase::class);
use PKP\form\Form;
use PKP\form\validation\FormValidator;
use PKP\form\validation\FormValidatorLength;

test('is valid', function () {
    $form = new Form('some template');
    $form->setData('testData', 'test');

    // Encode the tests to be run against the validator
    $tests = [
        ['==', 4, true],
        ['==', 5, false],
        ['==', 3, false],
        ['!=', 4, false],
        ['!=', 5, true],
        ['!=', 3, true],
        ['<', 5, true],
        ['<', 4, false],
        ['>', 3, true],
        ['>', 4, false],
        ['<=', 4, true],
        ['<=', 5, true],
        ['<=', 3, false],
        ['>=', 4, true],
        ['>=', 3, true],
        ['>=', 5, false],
        ['...', 3, false]
    ];

    foreach ($tests as $test) {
        $validator = new FormValidatorLength($form, 'testData', FormValidator::FORM_VALIDATOR_REQUIRED_VALUE, 'some.message.key', $test[0], $test[1]);
        self::assertSame($test[2], $validator->isValid());
    }

    // Test optional validation type
    $form->setData('testData', '');
    $validator = new FormValidatorLength($form, 'testData', FormValidator::FORM_VALIDATOR_OPTIONAL_VALUE, 'some.message.key', '==', 4);
    self::assertTrue($validator->isValid());
});

<?php

uses(\PKP\tests\PKPTestCase::class);
use PKP\validation\ValidatorISSN;


test('validator i s s n', function () {
    $validator = new ValidatorISSN();
    self::assertTrue($validator->isValid('0378-5955'));
    // Valid
    self::assertFalse($validator->isValid('0378-5955f'));
    // Overlong
    self::assertFalse($validator->isValid('03785955'));
    // Missing dash
    self::assertFalse($validator->isValid('1234-5678'));
    // Wrong check digit
    self::assertTrue($validator->isValid('0031-790X'));
    // Check digit is X
    self::assertTrue($validator->isValid('1945-2020'));
    // Check digit is 0
});

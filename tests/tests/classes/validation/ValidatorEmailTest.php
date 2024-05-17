<?php

uses(\PKP\tests\PKPTestCase::class);
use PKP\validation\ValidatorEmail;


test('validator email', function () {
    $validator = new ValidatorEmail();
    self::assertTrue($validator->isValid('some.address@gmail.com'));
    self::assertTrue($validator->isValid('anything@localhost'));
    self::assertTrue($validator->isValid("allowedchars!#$%&'*+./=?^_`{|}@gmail.com"));
    self::assertTrue($validator->isValid('"quoted.username"@gmail.com'));
    self::assertFalse($validator->isValid('anything else'));
    self::assertFalse($validator->isValid('double@@gmail.com'));
    self::assertFalse($validator->isValid('no"quotes"in.middle@gmail.com'));
});

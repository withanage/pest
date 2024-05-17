<?php

uses(\PKP\tests\PKPTestCase::class);
use PKP\validation\ValidatorUrl;


test('validator url and uri', function () {
    $validator = new ValidatorUrl();
    self::assertTrue($validator->isValid('ftp://some.download.com/'));
    self::assertTrue($validator->isValid('http://some.site.org/'));
    self::assertTrue($validator->isValid('https://some.site.org/'));
    self::assertTrue($validator->isValid('gopher://another.site.org/'));
    self::assertFalse($validator->isValid('anything else'));
    self::assertTrue($validator->isValid('http://189.63.74.2/'));
    self::assertTrue($validator->isValid('http://257.63.74.2/'));
});

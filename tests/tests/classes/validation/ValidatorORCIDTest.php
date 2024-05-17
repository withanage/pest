<?php

uses(\PKP\tests\PKPTestCase::class);
use PKP\validation\ValidatorORCID;


test('validator o r c i d', function () {
    $validator = new ValidatorORCID();
    self::assertFalse($validator->isValid('http://orcid.org/0000-0002-1825-0097'));
    // Invalid (http)
    self::assertTrue($validator->isValid('https://orcid.org/0000-0002-1825-0097'));
    // Valid (https)
    self::assertFalse($validator->isValid('ftp://orcid.org/0000-0002-1825-0097'));
    // Invalid (FTP scheme)
    self::assertTrue($validator->isValid('https://orcid.org/0000-0002-1694-233X'));
    // Valid, with an X in the last digit
    self::assertFalse($validator->isValid('0000-0002-1694-233X'));
    // Missing URI component
    self::assertFalse($validator->isValid('000000021694233X'));
    // Missing dashes, URI component
});

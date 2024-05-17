<?php

uses(\PKP\tests\PKPTestCase::class);
use PKP\validation\ValidatorTypeDescription;


test('instantiate and check', function () {
    $typeDescription = new ValidatorTypeDescription('email');
    self::assertTrue($typeDescription->isCompatible('jerico.dev@gmail.com'));
    self::assertFalse($typeDescription->isCompatible('another string'));
});

test('instantiate and check with parameters', function () {
    $typeDescription = new ValidatorTypeDescription('regExp("/123/")');
    self::assertFalse($typeDescription->checkType('some string'));
    self::assertFalse($typeDescription->checkType(new \stdClass()));
    self::assertTrue($typeDescription->checkType('123'));
    self::assertFalse($typeDescription->checkType('abc'));
});

/**
 * Provides test data
 */
dataset('typeDescriptorDataProvider', function () {
    return [
        'Invalid name' => ['email(xyz]'],
        'Invalid casing' => ['Email'],
        'Invalid character' => ['email&'],
    ];
});

test('instantiate with invalid type descriptor', function (string $type) {
    $this->expectException(\Exception::class);
    // Trying to instantiate a "validator" type description with an invalid type name "$type"
    $typeDescription = new ValidatorTypeDescription($type);
})->with('typeDescriptorDataProvider');

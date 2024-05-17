<?php

uses(\PKP\tests\PKPTestCase::class);
use \PKP\doi\DoiGenerator;

test('encode doi', function () {
    // Provides public access to protected method for testing
    $doiUtils = new class () extends DoiGenerator {
        function base32EncodeSuffix(int $number) : string
        {
            return base32EncodeSuffix($number);
        }
    };
    $number = 123;

    self::assertEquals('00003v20', base32EncodeSuffix($number));
    self::assertEquals('00003v20', base32EncodeSuffix((string) $number));
    self::assertMatchesRegularExpression(
        '/^[0-9abcdefghjkmnpqrstvwxyz]{6}[0-9]{2}$/',
        DoiGenerator::encodeSuffix()
    );
});

test('decode doi', function () {
    $validSuffix = DoiGenerator::encodeSuffix();
    $decodedValidSuffix = DoiGenerator::decodeSuffix($validSuffix);
    self::assertIsNumeric($decodedValidSuffix);

    $invalidSuffix = '00003v25';
    $decodedInvalidSuffix = DoiGenerator::decodeSuffix($invalidSuffix);
    self::assertNull($decodedInvalidSuffix);
});

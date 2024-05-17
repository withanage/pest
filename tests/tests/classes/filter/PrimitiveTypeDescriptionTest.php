<?php

uses(\PKP\tests\PKPTestCase::class);
use PKP\filter\PrimitiveTypeDescription;

test('instantiate and check', function () {
    $typeDescription = new PrimitiveTypeDescription('string');
    self::assertTrue($typeDescription->isCompatible('some string'));
    self::assertFalse($typeDescription->isCompatible(5));
    self::assertFalse($typeDescription->isCompatible([5]));

    self::assertEquals('string', $typeDescription->getTypeName());
    self::assertEquals('primitive::string', $typeDescription->getTypeDescription());

    $typeDescription = new PrimitiveTypeDescription('integer');
    self::assertTrue($typeDescription->isCompatible(2));
    self::assertFalse($typeDescription->isCompatible('some string'));
    self::assertFalse($typeDescription->isCompatible(5.5));
    self::assertFalse($typeDescription->isCompatible(new stdClass()));

    $typeDescription = new PrimitiveTypeDescription('float');
    self::assertTrue($typeDescription->isCompatible(2.5));
    self::assertFalse($typeDescription->isCompatible('some string'));
    self::assertFalse($typeDescription->isCompatible(5));

    $typeDescription = new PrimitiveTypeDescription('boolean');
    self::assertTrue($typeDescription->isCompatible(true));
    self::assertTrue($typeDescription->isCompatible(false));
    self::assertFalse($typeDescription->isCompatible(1));
    self::assertFalse($typeDescription->isCompatible(''));

    $typeDescription = new PrimitiveTypeDescription('integer[]');
    self::assertTrue($typeDescription->isCompatible([2]));
    self::assertTrue($typeDescription->isCompatible([2, 5]));
    self::assertFalse($typeDescription->isCompatible(2));

    $typeDescription = new PrimitiveTypeDescription('integer[1]');
    self::assertTrue($typeDescription->isCompatible([2]));
    self::assertFalse($typeDescription->isCompatible([2, 5]));
    self::assertFalse($typeDescription->isCompatible(2));
});

/**
 * Provides test data
 */
dataset('typeDescriptorDataProvider', function () {
    return [
        'An unknown type name will cause an error' => ['xyz'],
        'We don\'t allow multi-dimensional arrays' => ['integer[][]'],
        'An invalid cardinality will also cause an error' => ['integer[x]'],
    ];
});

test('instantiate with invalid type descriptor', function (string $type) {
    $this->expectException(\Exception::class);
    // Trying to instantiate a "primitive" type description with an invalid type name "$type"
    $typeDescription = new PrimitiveTypeDescription($type);
})->with('typeDescriptorDataProvider');

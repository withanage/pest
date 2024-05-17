<?php

uses(\PKP\tests\PKPTestCase::class);
use \PKP\tests\classes\filter\TestClass2;
use \PKP\tests\classes\filter\TestClass1;
use PKP\filter\TypeDescriptionFactory;

test('instantiate type description', function () {
    $typeDescriptionFactory = TypeDescriptionFactory::getInstance();

    // Instantiate a primitive type
    $typeDescription = $typeDescriptionFactory->instantiateTypeDescription('primitive::string');
    self::assertInstanceOf('PrimitiveTypeDescription', $typeDescription);
    self::assertTrue($typeDescription->isCompatible($object = 'some string'));
    self::assertFalse($typeDescription->isCompatible($object = 5));

    // Instantiate a class type
    $typeDescription = $typeDescriptionFactory->instantiateTypeDescription('class::lib.pkp.tests.classes.filter.TestClass1');
    self::assertInstanceOf('ClassTypeDescription', $typeDescription);
    $compatibleObject = new TestClass1();
    $wrongObject = new TestClass2();
    self::assertTrue($typeDescription->isCompatible($compatibleObject));
    self::assertFalse($typeDescription->isCompatible($wrongObject));

    // Test invalid descriptions
    self::assertNull($typeDescriptionFactory->instantiateTypeDescription('string'));
    self::assertNull($typeDescriptionFactory->instantiateTypeDescription('unknown-namespace::xyz'));
});

<?php

uses(\PKP\tests\PKPTestCase::class);
use \PKP\tests\classes\filter\TestClass2;
use \PKP\tests\classes\filter\TestClass1;
use PKP\filter\ClassTypeDescription;

test('instantiate and check', function () {
    $typeDescription = new ClassTypeDescription('lib.pkp.tests.classes.filter.TestClass1');
    $compatibleObject = new TestClass1();
    $wrongObject = new TestClass2();
    self::assertTrue($typeDescription->isCompatible($compatibleObject));
    self::assertFalse($typeDescription->isCompatible($wrongObject));
});

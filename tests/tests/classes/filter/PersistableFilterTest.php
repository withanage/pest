<?php

uses(\PKP\tests\PKPTestCase::class);
use PKP\filter\EmailFilterSetting;
use PKP\filter\PersistableFilter;
use PKP\filter\TypeDescriptionFactory;

test('instantiation and execute', function () {
    $constructorArg = PersistableFilter::tempGroup(
        'class::lib.pkp.tests.classes.filter.TestClass1',
        'class::lib.pkp.tests.classes.filter.TestClass2'
    );
    $testFilter = new PersistableFilter($constructorArg);

    // Test getters/setters that are not implicitly tested by other tests
    self::assertInstanceOf('FilterGroup', $testFilter->getFilterGroup());
    $testFilter->setDisplayName('Some other display name');
    $testFilter->setIsTemplate(1);
    self::assertTrue($testFilter->getIsTemplate());
    self::assertEquals(0, $testFilter->getParentFilterId());
    $testFilter->setParentFilterId(1);
    self::assertEquals(1, $testFilter->getParentFilterId());

    // Test settings
    self::assertFalse($testFilter->hasSettings());
    $testSetting = new EmailFilterSetting('testEmail', 'Test Email', 'Test Email is required');
    $testSetting2 = new EmailFilterSetting('testEmail2', 'Test Email2', 'Test Email2 is required');
    $testSetting2->setIsLocalized(true);
    $testFilter->addSetting($testSetting);
    $testFilter->addSetting($testSetting2);
    self::assertEquals(['testEmail' => $testSetting, 'testEmail2' => $testSetting2], $testFilter->getSettings());
    self::assertTrue($testFilter->hasSettings());
    self::assertEquals(['testEmail'], $testFilter->getSettingNames());
    self::assertEquals(['testEmail2'], $testFilter->getLocalizedSettingNames());
    self::assertTrue($testFilter->hasSetting('testEmail'));
    self::assertEquals($testSetting, $testFilter->getSetting('testEmail'));

    // Test type validation.
    $typeDescriptionFactory = TypeDescriptionFactory::getInstance();
    $inputTypeDescription = 'class::lib.pkp.tests.classes.filter.TestClass1';
    $outputTypeDescription = 'class::lib.pkp.tests.classes.filter.TestClass2';
    self::assertEquals($inputTypeDescription, $testFilter->getInputType()->getTypeDescription());
    self::assertEquals($outputTypeDescription, $testFilter->getOutputType()->getTypeDescription());
});

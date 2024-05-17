<?php

uses(\PKP\tests\PKPTestCase::class);
use \PKP\tests\classes\filter\TestClass2;
use \PKP\tests\classes\filter\TestClass1;
use PKP\filter\Filter;

test('instantiation and execute', function () {
    $mockFilter = getFilterMock();

    // Test getters/setters that are not implicitly tested by other tests
    self::assertEquals('Mock_Filter_', substr($mockFilter->getDisplayName(), 0, 12));
    $mockFilter->setDisplayName('Some other display name');
    self::assertEquals('Some other display name', $mockFilter->getDisplayName());
    $mockFilter->setSequence(5);
    self::assertEquals(5, $mockFilter->getSequence());

    // Test errors
    $mockFilter->addError('some error message');
    $mockFilter->addError('a second error message');
    $expectedErrors = [
        'some error message',
        'a second error message'
    ];
    self::assertEquals($expectedErrors, $mockFilter->getErrors());
    self::assertTrue($mockFilter->hasErrors());
    $mockFilter->clearErrors();
    self::assertEquals([], $mockFilter->getErrors());

    // Test type validation.
    $inputTypeDescription = 'class::lib.pkp.tests.classes.filter.TestClass1';
    $outputTypeDescription = 'class::lib.pkp.tests.classes.filter.TestClass2';
    self::assertEquals($inputTypeDescription, $mockFilter->getInputType()->getTypeDescription());
    self::assertEquals($outputTypeDescription, $mockFilter->getOutputType()->getTypeDescription());

    // Test execution without runtime requirements
    $testInput = new TestClass1();
    $testInput->testField = 'some filter input';
    self::assertInstanceOf('TestClass2', $testOutput = $mockFilter->execute($testInput));

    self::assertEquals(getTestOutput(), $testOutput);
    self::assertEquals($testInput, $mockFilter->getLastInput());
    self::assertEquals(getTestOutput(), $mockFilter->getLastOutput());

    // Test execution without runtime requirements
    $mockFilter = getFilterMock();
    $mockFilter->setData('phpVersionMin', '5.0.0');
    $testOutput = $mockFilter->execute($testInput);
    $runtimeEnvironment = $mockFilter->getRuntimeEnvironment();
    self::assertEquals('5.0.0', $runtimeEnvironment->getPhpVersionMin());

    // Do the same again but this time set the runtime
    // environment via a RuntimeEnvironment object.
    $mockFilter = getFilterMock();
    $mockFilter->setRuntimeEnvironment($runtimeEnvironment);
    $testOutput = $mockFilter->execute($testInput);
    $runtimeEnvironment = $mockFilter->getRuntimeEnvironment();
    self::assertEquals('5.0.0', $runtimeEnvironment->getPhpVersionMin());
    self::assertEquals('5.0.0', $mockFilter->getData('phpVersionMin'));

    // Test unsupported input
    $unsupportedInput = new TestClass2();

    $this->expectExceptionMessageMatches(self::localeToRegExp(__('filter.input.error.notSupported')));
    self::assertNull($mockFilter->execute($unsupportedInput));
    self::assertNull($mockFilter->getLastInput());
    self::assertNull($mockFilter->getLastOutput());

    // Test unsupported output
    $mockFilter = getFilterMock('class::lib.pkp.tests.classes.filter.TestClass1');
    self::assertNull($mockFilter->execute($testInput));
    self::assertEquals($testInput, $mockFilter->getLastInput());
    self::assertNull($mockFilter->getLastOutput());
});

test('unsupported environment', function () {
    $mockFilter = getFilterMock();
    $mockFilter->setData('phpVersionMin', '20.0.0');
    $this->expectExceptionMessage(__('filter.error.missingRequirements'));
    $mockFilter->execute($testInput);
});

/**
 * This method will be called to replace the abstract
 * process() method of our test filter.
 *
 * @return object
 */
function processCallback($input)
{
    return getTestOutput();
}

/**
 * Generate a test object.
 *
 * @return object
 */
function getTestOutput()
{
    static $output;
    if (is_null($output)) {
        // Create a test object as output
        $output = new TestClass2();
        $output->testField = 'some filter result';
    }
    return $output;
}

/**
 * Create a mock filter for testing
 *
 * @return Filter
 */
function getFilterMock($outputType = 'class::lib.pkp.tests.classes.filter.TestClass2')
{
    // Mock the abstract filter class
    $mockFilter = $this->getMockBuilder(Filter::class)
        ->onlyMethods(['process'])
        ->setConstructorArgs(['class::lib.pkp.tests.classes.filter.TestClass1', $outputType])
        ->getMock();

    // Set the filter processor.
    $mockFilter->expects($this->any())
        ->method('process')
        ->will($this->returnCallback(processCallback(...)));

    return $mockFilter;
}

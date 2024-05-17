<?php

uses(\PKP\tests\PKPTestCase::class);
use PKP\tests\PKPTestHelper;
use PKP\xslt\XMLTypeDescription;


/**
 * @see PHPUnit_Framework_TestCase::tearDown()
 */
afterEach(function () {
    PKPTestHelper::xdebugScream(true);
});

test('instantiate and check', function () {
    // Xdebug's scream parameter will disable the @ operator
    // that we need for XML validation.
    PKPTestHelper::xdebugScream(false);

    // Test with dtd validation
    $typeDescription = new XMLTypeDescription('dtd');
    $testXmlDom = new \DOMDocument('1.0', 'utf-8');
    $testXmlDom->load(dirname(__FILE__) . '/dtdsample-valid.xml');
    self::assertTrue($typeDescription->isCompatible($testXmlDom));
    $testXmlDom->load(dirname(__FILE__) . '/dtdsample-invalid.xml');

    /** @var \Throwable $e */
    $exception = null;
    try {
        $typeDescription->isCompatible($testXmlDom);
    } catch(\Throwable $exception) {
    }
    self::assertMatchesRegularExpression('/element collection content does not follow the DTD/i', $exception?->getMessage());

    // Test with xsd validation
    $typeDescription = new XMLTypeDescription('schema(' . dirname(__FILE__) . '/xsdsample.xsd)');
    $testXmlDom = new \DOMDocument('1.0', 'utf-8');
    $testXmlDom->load(dirname(__FILE__) . '/xsdsample-valid.xml');
    self::assertTrue($typeDescription->isCompatible($testXmlDom));
    $testXmlDom->load(dirname(__FILE__) . '/xsdsample-invalid.xml');
    self::assertFalse($typeDescription->isCompatible($testXmlDom));

    // Test with rng validation
    $typeDescription = new XMLTypeDescription('relax-ng(' . dirname(__FILE__) . '/rngsample.rng)');
    $testXmlDom = new \DOMDocument('1.0', 'utf-8');
    $testXmlDom->load(dirname(__FILE__) . '/rngsample-valid.xml');
    self::assertTrue($typeDescription->isCompatible($testXmlDom));
    $testXmlDom->load(dirname(__FILE__) . '/rngsample-invalid.xml');
    self::assertFalse($typeDescription->isCompatible($testXmlDom));

    // Try passing in the document as a string
    $document =
      '<addressBook>
            <card>
              <name>John Smith</name>
              <email>js@example.com</email>
            </card>
            <card>
              <name>Fred Bloggs</name>
              <email>fb@example.net</email>
            </card>
          </addressBook>';
    self::assertTrue($typeDescription->isCompatible($document));

    // Test without schema validation
    $typeDescription = new XMLTypeDescription('*');
    $testXmlDom = new \DOMDocument('1.0', 'utf-8');
    $testXmlDom->load(dirname(__FILE__) . '/rngsample-valid.xml');
    self::assertTrue($typeDescription->isCompatible($testXmlDom));
    $testXmlDom->load(dirname(__FILE__) . '/rngsample-invalid.xml');
    self::assertTrue($typeDescription->isCompatible($testXmlDom));
});

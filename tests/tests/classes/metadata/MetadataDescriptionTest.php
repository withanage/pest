<?php

uses(\PKP\tests\PKPTestCase::class);
use APP\core\Application;
use PKP\metadata\MetadataDescription;

beforeEach(function () {
    $this->metadataDescription = new MetadataDescription('lib.pkp.tests.classes.metadata.TestSchema', Application::ASSOC_TYPE_CITATION);
});

test('add statement', function () {
    foreach (self::$testStatements as $test) {
        $this->metadataDescription->addStatement($test[0], $test[1], $test[2]);
    }

    self::assertEquals(self::$testStatementsData, $this->metadataDescription->getAllData());
});

test('set statements', function () {
    $testStatements = self::$testStatementsData;
    $this->metadataDescription->setStatements($testStatements);
    self::assertEquals($testStatements, $this->metadataDescription->getAllData());

    $testStatements = [
        'not-translated-one' => 'nto-new',
        'not-translated-many' => [
            0 => 'ntm1-new',
            1 => 'ntm2-new'
        ],
        'translated-one' => [
            'en' => 'to_en-new',
            'de' => 'to_de-new'
        ],
        'translated-many' => [
            'en' => [
                0 => 'tm1_en-new',
                1 => 'tm2_en-new'
            ],
            'de' => [
                0 => 'tm1_de-new',
                1 => 'tm2_de-new'
            ]
        ]
    ];

    // Trying to replace a property with METADATA_DESCRIPTION_REPLACE_NOTHING
    // should provoke an error.
    $previousData = self::$testStatementsData;
    self::assertFalse($this->metadataDescription->setStatements($testStatements, MetadataDescription::METADATA_DESCRIPTION_REPLACE_NOTHING));
    self::assertEquals($previousData, $this->metadataDescription->getAllData());

    // Unset the offending property and try again - this should work
    $previousData = self::$testStatementsData;
    unset($previousData['not-translated-one']);
    unset($previousData['translated-one']);
    $this->metadataDescription->setAllData($previousData);
    $expectedResult = self::$testStatementsData;
    $expectedResult['not-translated-many'][] = 'ntm1-new';
    $expectedResult['not-translated-many'][] = 'ntm2-new';
    unset($expectedResult['translated-one']);
    $expectedResult['translated-one']['en'] = 'to_en-new';
    $expectedResult['translated-one']['de'] = 'to_de-new';
    $expectedResult['translated-many']['en'][] = 'tm1_en-new';
    $expectedResult['translated-many']['en'][] = 'tm2_en-new';
    $expectedResult['translated-many']['de'][] = 'tm1_de-new';
    $expectedResult['translated-many']['de'][] = 'tm2_de-new';
    unset($expectedResult['not-translated-one']);
    $expectedResult['not-translated-one'] = 'nto-new';
    self::assertTrue($this->metadataDescription->setStatements($testStatements, MetadataDescription::METADATA_DESCRIPTION_REPLACE_NOTHING));
    self::assertEquals($expectedResult, $this->metadataDescription->getAllData());

    // Using the default replacement level (METADATA_DESCRIPTION_REPLACE_PROPERTY)
    $previousData = self::$testStatementsData;
    $this->metadataDescription->setAllData($previousData);

    //unset($expectedResult['not-translated-many']);
    //$expectedResult['not-translated-many'] = array('ntm1-new', 'ntm2-new');
    self::assertTrue($this->metadataDescription->setStatements($testStatements));
    self::assertEquals($testStatements, $this->metadataDescription->getAllData());

    // Now test METADATA_DESCRIPTION_REPLACE_ALL
    self::assertTrue($this->metadataDescription->setStatements($testStatements, MetadataDescription::METADATA_DESCRIPTION_REPLACE_ALL));
    self::assertEquals($testStatements, $this->metadataDescription->getAllData());

    // Test that an error in the test statements maintains the previous state
    // of the description.
    // 1) Set some initial state (and make a non-referenced copy for later comparison)
    $previousData = ['non-translated-one' => 'previous-value'];
    $previousDataCopy = $previousData;
    $this->metadataDescription->setAllData($previousData);

    // 2) Create invalid test statement
    $testStatements['non-existent-property'] = 'some-value';

    // 3) Make sure that the previous data will always be restored when
    //    an error occurs.
    self::assertFalse($this->metadataDescription->setStatements($testStatements));
    self::assertEquals($previousDataCopy, $this->metadataDescription->getAllData());
    self::assertFalse($this->metadataDescription->setStatements($testStatements, true));
    self::assertEquals($previousDataCopy, $this->metadataDescription->getAllData());
});

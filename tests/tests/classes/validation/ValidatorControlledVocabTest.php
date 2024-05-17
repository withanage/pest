<?php

uses(\PKP\tests\PKPTestCase::class);
use APP\core\Application;
use PHPUnit\Framework\MockObject\MockObject;
use PKP\controlledVocab\ControlledVocab;
use PKP\controlledVocab\ControlledVocabDAO;
use PKP\db\DAORegistry;
use PKP\validation\ValidatorControlledVocab;


/**
 * @see PKPTestCase::getMockedDAOs()
 */
function getMockedDAOs() : array
{
    return [...getMockedDAOs(), 'ControlledVocabDAO'];
}

test('validator controlled vocab', function () {
    // Mock a ControlledVocab object
    /** @var ControlledVocab|MockObject */
    $mockControlledVocab = $this->getMockBuilder(ControlledVocab::class)
        ->onlyMethods(['enumerate'])
        ->getMock();
    $mockControlledVocab->setId(1);
    $mockControlledVocab->setAssocType(Application::ASSOC_TYPE_CITATION);
    $mockControlledVocab->setAssocId(333);
    $mockControlledVocab->setSymbolic('testVocab');

    // Set up the mock enumerate() method
    $mockControlledVocab->expects($this->any())
        ->method('enumerate')
        ->will($this->returnValue([1 => 'vocab1', 2 => 'vocab2']));

    // Mock the ControlledVocabDAO
    $mockControlledVocabDao = $this->getMockBuilder(ControlledVocabDAO::class)
        ->onlyMethods(['getBySymbolic'])
        ->getMock();

    // Set up the mock getBySymbolic() method
    $mockControlledVocabDao->expects($this->any())
        ->method('getBySymbolic')
        ->with('testVocab', Application::ASSOC_TYPE_CITATION, 333)
        ->will($this->returnValue($mockControlledVocab));

    DAORegistry::registerDAO('ControlledVocabDAO', $mockControlledVocabDao);

    $validator = new ValidatorControlledVocab('testVocab', Application::ASSOC_TYPE_CITATION, 333);
    self::assertTrue($validator->isValid('1'));
    self::assertTrue($validator->isValid('2'));
    self::assertFalse($validator->isValid('3'));
});

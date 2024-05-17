<?php

uses(\PKP\tests\PKPTestCase::class);
use PKP\citation\CitationListTokenizerFilter;

test('citation list tokenizer filter', function () {
    $tokenizer = new CitationListTokenizerFilter();
    $rawCitationList = "\t1. citation1\n\n2 citation2\r\n 3) citation3\n[4]citation4";
    $expectedResult = [
        'citation1',
        'citation2',
        'citation3',
        'citation4'
    ];
    self::assertEquals($expectedResult, $tokenizer->process($rawCitationList));

    $rawCitationList = '';
    self::assertEquals([], $tokenizer->process($rawCitationList));
});

<?php

uses(\PKP\tests\PKPTestCase::class);
use APP\publication\DAO;
use PKP\citation\CitationDAO;
use PKP\services\PKPSchemaService;
use PKP\submission\SubmissionAgencyDAO;
use PKP\submission\SubmissionDisciplineDAO;
use PKP\submission\SubmissionKeywordDAO;
use PKP\submission\SubmissionSubjectDAO;

/**
 * @see PKPTestCase::setUp()
 */
beforeEach(function () {
    $this->publication = (new DAO(
        new SubmissionKeywordDAO(),
        new SubmissionSubjectDAO(),
        new SubmissionDisciplineDAO(),
        new SubmissionAgencyDAO(),
        new CitationDAO(),
        new PKPSchemaService()
    ))->newDataObject();
});

/**
 * @see PKPTestCase::tearDown()
 */
afterEach(function () {
    unset($this->publication);
});

test('page array', function () {
    $expected = [['i', 'ix'], ['6', '11'], ['19'], ['21']];

    // strip prefix and spaces
    $this->publication->setData('pages', 'pg. i-ix, 6-11, 19, 21');
    $pageArray = $this->publication->getPageArray();
    expect($pageArray)->toBe($expected);

    // no spaces
    $this->publication->setData('pages', 'i-ix,6-11,19,21');
    $pageArray = $this->publication->getPageArray();
    expect($pageArray)->toBe($expected);

    // double-hyphen
    $this->publication->setData('pages', 'i--ix,6--11,19,21');
    $pageArray = $this->publication->getPageArray();
    expect($pageArray)->toBe($expected);

    // single page
    $expected = [['16']];
    $this->publication->setData('pages', '16');
    $pageArray = $this->publication->getPageArray();
    expect($pageArray)->toBe($expected);

    // spaces in a range
    $expected = [['16', '20']];
    $this->publication->setData('pages', '16 - 20');
    $pageArray = $this->publication->getPageArray();
    expect($pageArray)->toBe($expected);

    // pages are alphanumeric
    $expected = [['a6', 'a12'], ['b43']];
    $this->publication->setData('pages', 'a6-a12,b43');
    $pageArray = $this->publication->getPageArray();
    expect($pageArray)->toBe($expected);

    // inconsisent formatting
    $this->publication->setData('pages', 'pp:  a6 -a12,   b43');
    $pageArray = $this->publication->getPageArray();
    expect($pageArray)->toBe($expected);
    $this->publication->setData('pages', '  a6 -a12,   b43 ');
    $pageArray = $this->publication->getPageArray();
    expect($pageArray)->toBe($expected);

    // empty-ish values
    $expected = [];
    $this->publication->setData('pages', '');
    $pageArray = $this->publication->getPageArray();
    expect($pageArray)->toBe($expected);
    $this->publication->setData('pages', ' ');
    $pageArray = $this->publication->getPageArray();
    expect($pageArray)->toBe($expected);
    $expected = [['0']];
    $this->publication->setData('pages', '0');
    $pageArray = $this->publication->getPageArray();
    expect($pageArray)->toBe($expected);
});

test('get starting page', function () {
    $expected = 'i';

    // strip prefix and spaces
    $this->publication->setData('pages', 'pg. i-ix, 6-11, 19, 21');
    $startingPage = $this->publication->getStartingPage();
    expect($startingPage)->toBe($expected);

    // no spaces
    $this->publication->setData('pages', 'i-ix,6-11,19,21');
    $startingPage = $this->publication->getStartingPage();
    expect($startingPage)->toBe($expected);

    // double-hyphen
    $this->publication->setData('pages', 'i--ix,6--11,19,21');
    $startingPage = $this->publication->getStartingPage();
    expect($startingPage)->toBe($expected);

    // single page
    $expected = '16';
    $this->publication->setData('pages', '16');
    $startingPage = $this->publication->getStartingPage();
    expect($startingPage)->toBe($expected);

    // spaces in a range
    $this->publication->setData('pages', '16 - 20');
    $startingPage = $this->publication->getStartingPage();
    expect($startingPage)->toBe($expected);

    // pages are alphanumeric
    $expected = 'a6';
    $this->publication->setData('pages', 'a6-a12,b43');
    $startingPage = $this->publication->getStartingPage();
    expect($startingPage)->toBe($expected);

    // inconsisent formatting
    $this->publication->setData('pages', 'pp:  a6 -a12,   b43');
    $startingPage = $this->publication->getStartingPage();
    expect($startingPage)->toBe($expected);
    $this->publication->setData('pages', '  a6 -a12,   b43 ');
    $startingPage = $this->publication->getStartingPage();
    expect($startingPage)->toBe($expected);

    // empty-ish values
    $expected = '';
    $this->publication->setData('pages', '');
    $startingPage = $this->publication->getStartingPage();
    expect($startingPage)->toBe($expected);
    $this->publication->setData('pages', ' ');
    $startingPage = $this->publication->getStartingPage();
    expect($startingPage)->toBe($expected);
    $expected = '0';
    $this->publication->setData('pages', '0');
    $startingPage = $this->publication->getStartingPage();
    expect($startingPage)->toBe($expected);
});

test('get ending page', function () {
    $expected = '21';

    // strip prefix and spaces
    $this->publication->setData('pages', 'pg. i-ix, 6-11, 19, 21');
    $endingPage = $this->publication->getEndingPage();
    expect($endingPage)->toBe($expected);

    // no spaces
    $this->publication->setData('pages', 'i-ix,6-11,19,21');
    $endingPage = $this->publication->getEndingPage();
    expect($endingPage)->toBe($expected);

    // double-hyphen
    $this->publication->setData('pages', 'i--ix,6--11,19,21');
    $endingPage = $this->publication->getEndingPage();
    expect($endingPage)->toBe($expected);

    // single page
    $expected = '16';
    $this->publication->setData('pages', '16');
    $endingPage = $this->publication->getEndingPage();
    expect($endingPage)->toBe($expected);

    // spaces in a range
    $expected = '20';
    $this->publication->setData('pages', '16 - 20');
    $endingPage = $this->publication->getEndingPage();
    expect($endingPage)->toBe($expected);

    // pages are alphanumeric
    $expected = 'b43';
    $this->publication->setData('pages', 'a6-a12,b43');
    $endingPage = $this->publication->getEndingPage();
    expect($endingPage)->toBe($expected);

    // inconsisent formatting
    $this->publication->setData('pages', 'pp:  a6 -a12,   b43');
    $endingPage = $this->publication->getEndingPage();
    expect($endingPage)->toBe($expected);
    $this->publication->setData('pages', '  a6 -a12,   b43 ');
    $endingPage = $this->publication->getEndingPage();
    expect($endingPage)->toBe($expected);

    // empty-ish values
    $expected = '';
    $this->publication->setData('pages', '');
    $endingPage = $this->publication->getEndingPage();
    expect($endingPage)->toBe($expected);
    $this->publication->setData('pages', ' ');
    $endingPage = $this->publication->getEndingPage();
    expect($endingPage)->toBe($expected);
    $expected = '0';
    $this->publication->setData('pages', '0');
    $endingPage = $this->publication->getEndingPage();
    expect($endingPage)->toBe($expected);
});

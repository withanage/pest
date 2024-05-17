<?php

uses(\PKP\tests\PKPTestCase::class);
use Mockery\MockInterface;
use PKP\facades\Locale;
use PKP\i18n\LocaleConversion;
use PKP\i18n\LocaleMetadata;

beforeEach(function () {
    // Save the underlying Locale implementation and replaces it by a generic mock
    $this->_locale = Locale::getFacadeRoot();

    /** @var \PKP\i18n\Locale|MockInterface */
    $mock = Mockery::mock($this->_locale::class);
    $mock = $mock
        ->makePartial()
        // Custom locales
        ->shouldReceive('getLocales')
        ->andReturn(
            [
                'en' => _createMetadataMock('en', true),
                'pt_BR' => _createMetadataMock('pt_BR'),
                'pt_PT' => _createMetadataMock('pt_PT'),
                'de' => _createMetadataMock('de')
            ]
        )
        // Forward get() calls to the real locale, in order to use the already loaded translations
        ->shouldReceive('get')
        ->andReturnUsing(fn (...$args) => $this->_locale->get(...$args))
        // Custom supported locales
        ->shouldReceive('getSupportedLocales')
        ->andReturnUsing(fn () => $this->_supportedLocales)
        // Custom primary locale
        ->shouldReceive('getPrimaryLocale')
        ->andReturnUsing(fn () => $this->_primaryLocale)
        ->getMock();

    Locale::swap($mock);
});

afterEach(function () {
    // Restores the original locale instance
    Locale::swap($this->_locale);
});

function _createMetadataMock(string $locale, bool $isComplete = false) : MockInterface
{
    /** @var LocaleMetadata|MockInterface */
    $mock = Mockery::mock(LocaleMetadata::class, [$locale]);
    return $mock
        ->makePartial()
        ->shouldReceive('isComplete')
        ->andReturn($isComplete)
        ->getMock();
}

test('is locale complete', function () {
    self::assertTrue(Locale::getMetadata('en')->isComplete());
    self::assertFalse(Locale::getMetadata('pt_BR')->isComplete());
    self::assertNull(Locale::getMetadata('xx_XX'));
});

test('get locales', function () {
    $this->_primaryLocale = 'en_US';
    $expectedLocales = [
        'en' => 'English',
        'pt_BR' => 'Portuguese',
        'pt_PT' => 'Portuguese',
        'de' => 'German'
    ];
    $locales = array_map(fn (LocaleMetadata $locale) => $locale->getDisplayName(), Locale::getLocales());
    self::assertEquals($expectedLocales, $locales);
});

test('get locales with country name', function () {
    $expectedLocalesWithCountry = [
        'en' => 'English',
        'pt_BR' => 'Portuguese (Brazil)',
        'pt_PT' => 'Portuguese (Portugal)',
        'de' => 'German'
    ];
    $locales = array_map(fn (LocaleMetadata $locale) => $locale->getDisplayName(null, true), Locale::getLocales());
    self::assertEquals($expectedLocalesWithCountry, $locales);
});

test('get3 letter from2 letter iso language', function () {
    self::assertEquals('eng', LocaleConversion::get3LetterFrom2LetterIsoLanguage('en'));
    self::assertEquals('por', LocaleConversion::get3LetterFrom2LetterIsoLanguage('pt'));
    self::assertEquals('fre', LocaleConversion::get3LetterFrom2LetterIsoLanguage('fr'));
    self::assertNull(LocaleConversion::get3LetterFrom2LetterIsoLanguage('xx'));
});

test('get2 letter from3 letter iso language', function () {
    self::assertEquals('en', LocaleConversion::get2LetterFrom3LetterIsoLanguage('eng'));
    self::assertEquals('pt', LocaleConversion::get2LetterFrom3LetterIsoLanguage('por'));
    self::assertEquals('fr', LocaleConversion::get2LetterFrom3LetterIsoLanguage('fre'));
    self::assertNull(LocaleConversion::get2LetterFrom3LetterIsoLanguage('xxx'));
});

test('get3 letter iso from locale', function () {
    self::assertEquals('eng', LocaleConversion::get3LetterIsoFromLocale('en'));
    self::assertEquals('por', LocaleConversion::get3LetterIsoFromLocale('pt_BR'));
    self::assertEquals('por', LocaleConversion::get3LetterIsoFromLocale('pt_PT'));
    self::assertNull(LocaleConversion::get3LetterIsoFromLocale('xx_XX'));
});

test('get locale from3 letter iso', function () {
    // A locale that does not have to be disambiguated.
    self::assertEquals('en', LocaleConversion::getLocaleFrom3LetterIso('eng'));

    // The primary locale will be used if that helps to disambiguate.
    self::assertEquals('pt_BR', LocaleConversion::getLocaleFrom3LetterIso('por'));
    $this->_primaryLocale = 'pt_PT';
    self::assertEquals('pt_PT', LocaleConversion::getLocaleFrom3LetterIso('por'));

    // If the primary locale doesn't help then use the first supported locale found.
    $this->_primaryLocale = 'en';
    self::assertEquals('pt_BR', LocaleConversion::getLocaleFrom3LetterIso('por'));
    $this->_supportedLocales = ['en' => 'English', 'pt_PT' => 'Portuguese (Portugal)', 'pt_BR' => 'Portuguese (Brazil)'];
    self::assertEquals('pt_PT', LocaleConversion::getLocaleFrom3LetterIso('por'));

    // If the locale isn't even in the supported locales then use the first locale found.
    $this->_supportedLocales = ['en' => 'English'];
    self::assertEquals('pt_BR', LocaleConversion::getLocaleFrom3LetterIso('por'));

    // Unknown language.
    self::assertNull(LocaleConversion::getLocaleFrom3LetterIso('xxx'));
});

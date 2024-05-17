<?php

uses(\PKP\tests\PKPTestCase::class);
use PKP\config\Config;
use PKP\core\Core;

/**
 * @see PKPTestCase::getMockedRegistryKeys()
 */
function getMockedRegistryKeys() : array
{
    return [...getMockedRegistryKeys(), 'configData', 'configFile'];
}

test('get default config file name', function () {
    $expectedResult = Core::getBaseDir() . '/config.inc.php';
    self::assertEquals($expectedResult, Config::getConfigFileName());
});

test('set config file name', function () {
    Config::setConfigFileName('some_config');
    self::assertEquals('some_config', Config::getConfigFileName());
});

test('reload data with non existent config file', function () {
    Config::setConfigFileName('some_config');
    $this->expectExceptionMessage('Cannot read configuration file some_config');
    Config::reloadData();
});

test('reload data and get data', function () {
    Config::setConfigFileName('lib/pkp/tests/config/config.TEMPLATE.mysql.inc.php');
    $result = Config::reloadData();
    $expectedResult = [
        'installed' => true,
        'base_url' => 'https://pkp.sfu.ca/ojs',
        'session_cookie_name' => 'OJSSID',
        'session_lifetime' => 30,
        'scheduled_tasks' => false,
        'date_format_short' => 'Y-m-d',
        'date_format_long' => 'F j, Y',
        'datetime_format_short' => 'Y-m-d h:i A',
        'datetime_format_long' => 'F j, Y - h:i A',
        'allowed_hosts' => '["mydomain.org"]',
        'time_format' => 'h:i A',
    ];

    // We'll only check part of the configuration data to
    // keep the test less verbose.
    self::assertEquals($expectedResult, $result['general']);

    $result = & Config::getData();
    self::assertEquals($expectedResult, $result['general']);
});

test('get var', function () {
    Config::setConfigFileName('lib/pkp/tests/config/config.TEMPLATE.mysql.inc.php');
    self::assertEquals('mysqli', Config::getVar('database', 'driver'));
    self::assertNull(Config::getVar('general', 'non-existent-config-var'));
    self::assertNull(Config::getVar('non-existent-config-section', 'non-existent-config-var'));
});

test('get var from other config', function () {
    Config::setConfigFileName('lib/pkp/tests/config/config.TEMPLATE.pgsql.inc.php');
    self::assertEquals('pgsql', Config::getVar('database', 'driver'));
});

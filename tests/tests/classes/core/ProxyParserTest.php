<?php

uses(\PKP\tests\PKPTestCase::class);
use PKP\proxy\ProxyParser;

test('parsing for h t t p', function () {
    $fqdn = 'http://username:password@192.168.1.1:8080';
    $proxy = new ProxyParser();
    $proxy->parseFQDN($fqdn);

    expect($proxy->getProxy())->toEqual('tcp://192.168.1.1:8080');

    expect($proxy->getAuth())->toEqual(base64_encode('username:password'));
});

test('parsing for h t t p s', function () {
    $fqdn = 'https://username:password@192.168.1.1:8080';
    $proxy = new ProxyParser();
    $proxy->parseFQDN($fqdn);

    expect($proxy->getProxy())->toEqual('tcp://192.168.1.1:8080');

    expect($proxy->getAuth())->toEqual(base64_encode('username:password'));
});

test('non common proxy option', function () {
    $fqdn = 'udp://username:password@176.0.0.1:8040';
    $proxy = new ProxyParser();
    $proxy->parseFQDN($fqdn);

    expect($proxy->getProxy())->toEqual('udp://username:password@176.0.0.1:8040');
});

test('empty proxy option', function () {
    $proxy = new ProxyParser();
    $proxy->parseFQDN('');

    expect($proxy->getProxy())->toEqual('');
});

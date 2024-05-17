<?php

uses(\PKP\tests\PKPTestCase::class);
use PKP\cache\CacheManager;

test('get cache', function () {
    // Get the file cache.
    $fileCache = getCache();

    // No cache misses should be registered.
    self::assertTrue($this->cacheMisses == 0);

    // The cache has just been flushed by setUp. Try a get.
    $val1 = $fileCache->get(1);

    // Make sure the returned value was correct
    self::assertTrue($val1 == 'one');

    // Make sure we registered one cache miss
    self::assertTrue($this->cacheMisses == 1);

    // Try another get
    $val2 = $fileCache->get(2);

    // Make sure the value was correct
    self::assertTrue($val2 == 'two');

    // Make sure we didn't have to register another cache miss
    self::assertTrue($this->cacheMisses == 1);
});

test('cache miss', function () {
    // Get the file cache.
    $fileCache = getCache();

    // Try to get an item that's not in the cache
    $val1 = $fileCache->get(-1);

    // Make sure we registered one cache miss
    self::assertTrue($val1 == null);
    self::assertTrue($this->cacheMisses == 1);

    // Try another get of the same item
    $val2 = $fileCache->get(-1);

    // Check to see that we got it without a second miss
    self::assertTrue($val2 == null);

    // When an item isn't found, the cache is reset
    self::assertTrue($this->cacheMisses == 2);
});

//
// Helper functions
//
function _cacheMiss($cache, $id)
{
    $this->cacheMisses++;
    $cache->setEntireCache($this->testCacheContents);
    if (!isset($this->testCacheContents[$id])) {
        $cache->setCache($id, null);
        return null;
    }
    return $this->testCacheContents[$id];
}

//
// Protected methods.
//
beforeEach(function () {
    $this->cacheManager = CacheManager::getManager();
    $this->cacheMisses = 0;

    if (!is_writable($this->cacheManager->getFileCachePath())) {
        $this->markTestSkipped('File cache path not writable.');
    }
    $this->cacheManager->flush();
});

/**
 * Return a test cache.
 */
function getCache()
{
    return $this->cacheManager->getFileCache('testCache', 0, _cacheMiss(...));
}

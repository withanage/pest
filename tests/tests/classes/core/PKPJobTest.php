<?php

uses(\PKP\tests\PKPTestCase::class);
use Illuminate\Bus\PendingBatch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use PKP\config\Config;
use PKP\jobs\testJobs\TestJobFailure;
use PKP\jobs\testJobs\TestJobSuccess;

/**
 * @see PKPTestCase::setUp()
 */
beforeEach(function () {
    $this->originalErrorLog = ini_get('error_log');
    $this->tmpErrorLog = tmpfile();

    ini_set('error_log', stream_get_meta_data($this->tmpErrorLog)['uri']);
});

/**
 * @see PKPTestCase::tearDown()
 */
afterEach(function () {
    ini_set('error_log', $this->originalErrorLog);

});

test('job exception on sync', function () {
    $this->expectException(Exception::class);

    TestJobFailure::dispatchSync();
});

test('job dispatch', function () {
    Bus::fake();

    TestJobFailure::dispatch();
    TestJobSuccess::dispatch();

    Bus::assertDispatched(TestJobFailure::class);
    Bus::assertDispatched(TestJobSuccess::class);
});

test('job dispatch in chain', function () {
    Bus::fake();

    Bus::chain([
        new TestJobFailure(),
        new TestJobSuccess(),
    ])->dispatch();

    Bus::assertChained([
        TestJobFailure::class,
        TestJobSuccess::class,
    ]);
});

test('job dispatch in batch', function () {
    Bus::fake();

    Bus::batch([
        new TestJobSuccess(),
        new TestJobSuccess(),
        new TestJobFailure(),
        new TestJobFailure(),
    ])->name('test-jobs')->dispatch();

    Bus::assertBatched(function (PendingBatch $batch) {
        return $batch->name === 'test-jobs' && $batch->jobs->count() === 4;
    });
});

test('putting jobs at queue', function () {
    Queue::fake();

    $queue = Config::getVar('queues', 'default_queue', 'php-unit');

    $jobContent = 'exampleContent';

    Queue::push($jobContent, [], $queue);

    Queue::assertPushedOn($queue, $jobContent);
});

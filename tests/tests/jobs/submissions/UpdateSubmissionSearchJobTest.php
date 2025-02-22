<?php

uses(\PKP\tests\PKPTestCase::class);
use PKP\jobs\submissions\UpdateSubmissionSearchJob;
use Mockery\MockInterface;


test('run serialized job', function () {
    // Serializion from OJS 3.4.0
    $updateSubmissionSearchJob = unserialize('O:46:"PKP\jobs\submissions\UpdateSubmissionSearchJob":3:{s:15:" * submissionId";i:17;s:10:"connection";s:8:"database";s:5:"queue";s:5:"queue";}');

    // Ensure that the unserialized object is the right class
    expect($updateSubmissionSearchJob)->toBeInstanceOf(UpdateSubmissionSearchJob::class);

    // Mock the Submission facade to return a fake submission when Repo::submission()->get($id) is called
    $mock = Mockery::mock(app(\APP\submission\Repository::class))
        ->makePartial()
        ->shouldReceive('get')
        ->with(17) // Submission ID from serialization string
        ->andReturn(new \APP\submission\Submission())
        ->getMock();

    app()->instance(\APP\submission\Repository::class, $mock);

    // Test that the job can be handled without causing an exception.
    $updateSubmissionSearchJob->handle();
});

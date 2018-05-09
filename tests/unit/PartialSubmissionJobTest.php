<?php

namespace Firesphere\PartialUserforms\Tests;

use Firesphere\PartialUserforms\Jobs\PartialSubmissionJob;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use Symbiote\QueuedJobs\Services\QueuedJobService;

class PartialSubmissionJobTest extends SapphireTest
{
    /**
     * @var PartialSubmissionJob
     */
    protected $job;

    protected static $fixture_file = '../partialsubmissions.yml';

    public function testGetTitle()
    {
        $this->assertEquals('Export partial submissions to Email', $this->job->getTitle());
    }

    public function testProcess()
    {
        $this->assertTrue(method_exists($this->job, 'process'));
    }

    protected function setUp()
    {
        $this->usesDatabase = true;
        Config::modify()->set(QueuedJobService::class, 'use_shutdown_function', false);
        /** @var PartialSubmissionJob $job */
        $this->job = Injector::inst()->get(PartialSubmissionJob::class);
        return parent::setUp();
    }
}

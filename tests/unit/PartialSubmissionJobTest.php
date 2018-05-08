<?php

namespace Firesphere\PartialUserforms\Tests;

use Firesphere\PartialUserforms\Jobs\PartialSubmissionJob;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use Symbiote\QueuedJobs\Services\QueuedJobService;

class PartialSubmissionJobTest extends SapphireTest
{

    public function testGetTitle()
    {
        /** @var PartialSubmissionJob $job */
        $job = Injector::inst()->get(PartialSubmissionJob::class);

        $this->assertEquals('Export partial submissions to Email', $job->getTitle());
    }

    protected function setUp()
    {
        $this->usesDatabase = true;
        Config::modify()->set(QueuedJobService::class, 'use_shutdown_function', false);
        return parent::setUp();
    }
}

<?php

namespace Firesphere\PartialUserforms\Tests;

use Firesphere\PartialUserforms\Jobs\PartialSubmissionJob;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;

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

        return parent::setUp();
    }
}

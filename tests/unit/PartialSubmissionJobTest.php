<?php

namespace Firesphere\PartialUserforms\Tests;

use Firesphere\PartialUserforms\Jobs\PartialSubmissionJob;
use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\Debug;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\SiteConfig\SiteConfig;
use Symbiote\QueuedJobs\Services\QueuedJobService;

class PartialSubmissionJobTest extends SapphireTest
{
    protected static $fixture_file = '../fixtures/submission.yml';
    /**
     * @var PartialSubmissionJob
     */
    protected $job;

    public function testGetTitle()
    {
        $this->assertEquals('Export partial submissions to Email', $this->job->getTitle());
    }

    public function testProcess()
    {
        $this->assertTrue(method_exists($this->job, 'process'));
        $config = SiteConfig::current_site_config();
        $config->SendDailyEmail = true;
        $config->SendMailTo = 'test@example.com';
        $config->write();
        $this->job->process();

        $this->assertEmailSent('test@example.com');
        $this->assertFileExists('/tmp/Export of TestForm - 2018-01-01 12:00:00.csv');
    }

    public function testProcessNoMail()
    {
        $this->assertTrue(method_exists($this->job, 'process'));
        $config = SiteConfig::current_site_config();
        $config->SendDailyEmail = true;
        $config->SendMailTo = '';
        $config->write();
        $this->job->process();

        $messages = $this->job->getMessages();
        foreach ($messages as $message) {
            $this->assertContains('Can not process without valid email', $message);
        }
    }

    public function testIsSend()
    {
        $submission = $this->objFromFixture(PartialFormSubmission::class, 'submission1');
        $config = SiteConfig::current_site_config();
        $config->SendDailyEmail = true;
        $config->SendMailTo = 'test@example.com';
        $config->write();
        $this->job->process();

        $processedSubmission = $submission->get()->byID($submission->ID);

        $this->assertTrue((bool)$processedSubmission->IsSend);
    }

    public function testIsDeleted()
    {
        $submission = $this->objFromFixture(PartialFormSubmission::class, 'submission1');
        $config = SiteConfig::current_site_config();
        $config->SendDailyEmail = true;
        $config->CleanupAfterSend = true;
        $config->SendMailTo = 'test@example.com';
        $config->write();
        $this->job->process();
        $this->job->afterComplete();

        $processedSubmission = $submission->get()->byID($submission->ID);

        $this->assertNull($processedSubmission);
    }

    public function testNewJobCreated()
    {
        $config = SiteConfig::current_site_config();
        $config->SendDailyEmail = true;
        $config->CleanupAfterSend = true;
        $config->SendMailTo = 'test@example.com';
        $config->write();
        $this->job->process();
        $this->job->afterComplete();
    }

    protected function setUp()
    {
        $this->usesDatabase = true;
        Config::modify()->set(QueuedJobService::class, 'use_shutdown_function', false);
        DBDatetime::set_mock_now('2018-01-01 12:00:00');
        /** @var PartialSubmissionJob $job */
        $this->job = Injector::inst()->get(PartialSubmissionJob::class);

        return parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }
}

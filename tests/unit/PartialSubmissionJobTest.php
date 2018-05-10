<?php

namespace Firesphere\PartialUserforms\Tests;

use Firesphere\PartialUserforms\Jobs\PartialSubmissionJob;
use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\SiteConfig\SiteConfig;
use Symbiote\QueuedJobs\DataObjects\QueuedJobDescriptor;
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
        $id = $submission->write();
        $config = SiteConfig::current_site_config();
        $config->SendDailyEmail = true;
        $config->CleanupAfterSend = true;
        $config->SendMailTo = 'test@example.com';
        $config->write();
        $this->job->process();
        $this->job->afterComplete();

        $processedSubmission = $submission->get()->byID($id);

        $this->assertNull($processedSubmission);
    }

    public function testFilesRemoved()
    {
        $config = SiteConfig::current_site_config();
        $config->SendDailyEmail = true;
        $config->SendMailTo = 'test@example.com';
        $config->write();
        $this->job->process();
        $this->job->afterComplete();

        $this->assertFileNotExists('/tmp/Export of TestForm - 2018-01-01 12:00:00.csv');
    }

    public function testNewJobCreated()
    {
        $config = SiteConfig::current_site_config();
        $config->SendDailyEmail = true;
        $config->SendMailTo = 'test@example.com';
        $config->write();

        $this->job->process();
        $this->job->afterComplete();

        $jobs = QueuedJobDescriptor::get()->filter([
            'Implementation'         => PartialSubmissionJob::class,
            'StartAfter:GreaterThan' => DBDatetime::now()->Format(DBDatetime::ISO_DATETIME)
        ]);

        $this->assertEquals(1, $jobs->count());
        $this->assertEquals('2018-01-02 00:00:00', $jobs->first()->StartAfter);
    }

    public function testInvalidEmail()
    {
        $config = SiteConfig::current_site_config();
        $config->SendDailyEmail = true;
        $config->SendMailTo = 'test@example.com, error, non-existing, tester@example.com';
        $config->write();

        /** @var PartialSubmissionJob $job */
        $job = Injector::inst()->get(PartialSubmissionJob::class);

        $emails = $job->getAddresses();

        $this->assertArrayNotHasKey('error', $emails);
        $this->assertArrayNotHasKey('non-existing', $emails);
        $this->assertArrayNotHasKey(' test@example.com', $emails);
        $this->assertArrayHasKey('test@example.com', $emails);
        $this->assertArrayHasKey('tester@example.com', $emails);
    }

    public function testCommaSeparatedUsers()
    {
        $config = SiteConfig::current_site_config();
        $config->SendDailyEmail = true;
        $config->SendMailTo = 'test@example.com, tester@example.com , another@example.com';
        $config->write();

        $this->job->process();
        $this->assertEmailSent('test@example.com');
        $this->assertEmailSent('tester@example.com');
        $this->assertEmailSent('another@example.com');
    }

    protected function setUp()
    {
        parent::setUp();
        $this->usesDatabase = true;
        DBDatetime::set_mock_now('2018-01-01 12:00:00');
        /** @var PartialSubmissionJob $job */
        $this->job = Injector::inst()->get(PartialSubmissionJob::class);
        Config::modify()->set(QueuedJobService::class, 'use_shutdown_function', false);
    }

    protected function tearDown()
    {
        parent::tearDown();
    }
}

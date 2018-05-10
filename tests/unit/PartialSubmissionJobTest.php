<?php

namespace Firesphere\PartialUserforms\Tests;

use Firesphere\PartialUserforms\Jobs\PartialSubmissionJob;
use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\Security\Security;
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

    public function testSetup()
    {
        $this->job->setup();

        $this->assertInstanceOf(SiteConfig::class, $this->job->getConfig());
    }

    public function testProcess()
    {
        $this->assertTrue(method_exists($this->job, 'process'));
        SetupSiteConfig::setupSiteConfig('test@example.com', null, true);
        $this->job->setup();
        $this->job->process();

        $this->assertEmailSent('test@example.com');
        $this->assertFileExists('/tmp/Export of TestForm - 2018-01-01 12:00:00.csv');
    }

    public function testProcessNoMail()
    {
        $this->assertTrue(method_exists($this->job, 'process'));
        SetupSiteConfig::setupSiteConfig('', null, true);
        Security::setCurrentUser(null);
        $this->job->setup();
        $this->job->process();

        $messages = $this->job->getMessages();
        foreach ($messages as $message) {
            $this->assertContains('Can not process without valid email', $message);
        }
    }

    public function testProcessNotSetup()
    {
        SetupSiteConfig::setupSiteConfig('', null, false);
        Security::setCurrentUser(null);
        $this->job->setup();
        $this->job->process();

        $messages = $this->job->getMessages();
        foreach ($messages as $message) {
            $this->assertContains('Daily exports are not enabled', $message);
        }
    }

    public function testIsSend()
    {
        $submission = $this->objFromFixture(PartialFormSubmission::class, 'submission1');
        SetupSiteConfig::setupSiteConfig('test@example.com', null, true);

        $this->job->setup();
        $this->job->process();

        $processedSubmission = $submission->get()->byID($submission->ID);

        $this->assertTrue((bool)$processedSubmission->IsSend);
    }

    public function testIsDeleted()
    {
        $submission = $this->objFromFixture(PartialFormSubmission::class, 'submission1');
        $id = $submission->write();
        SetupSiteConfig::setupSiteConfig('test@example.com', null, true, true);
        $this->job->setup();
        $this->job->process();
        $this->job->afterComplete();

        $processedSubmission = $submission->get()->byID($id);

        $this->assertNull($processedSubmission);
    }

    public function testFilesRemoved()
    {
        SetupSiteConfig::setupSiteConfig('test@example.com', null, true);
        $this->job->setup();
        $this->job->process();
        $this->job->afterComplete();

        $this->assertFileNotExists('/tmp/Export of TestForm - 2018-01-01 12:00:00.csv');
    }

    public function testNewJobCreated()
    {
        SetupSiteConfig::setupSiteConfig('test@example.com', null, true);
        $this->job->setup();
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
        SetupSiteConfig::setupSiteConfig('test@example.com, error, non-existing, tester@example.com', null, true);

        /** @var PartialSubmissionJob $job */
        $job = new PartialSubmissionJob();
        $job->setup();
        $emails = $job->getAddresses();

        $expected = [
            'test@example.com',
            'tester@example.com'
        ];
        $this->assertEquals($expected, $emails);
    }

    public function testCommaSeparatedUsers()
    {
        SetupSiteConfig::setupSiteConfig('test@example.com, tester@example.com , another@example.com', null, true);

        $this->job->setup();
        $this->job->process();

        $this->assertEmailSent('test@example.com');
        $this->assertEmailSent('tester@example.com');
        $this->assertEmailSent('another@example.com');
    }

    public function testFromAddressSet()
    {
        SetupSiteConfig::setupSiteConfig('test@example.com', 'site@example.com', true);

        $this->job->setup();
        $this->job->process();
        $this->assertEmailSent('test@example.com', 'site@example.com');
    }

    public function testFromAddressNotSet()
    {
        SetupSiteConfig::setupSiteConfig('test@example.com', null, true);

        $this->job->setup();
        $this->job->process();
        $this->assertEmailSent('test@example.com', 'site@' . Director::host());
    }

    protected function setUp()
    {
        parent::setUp();
        $this->usesDatabase = true;
        DBDatetime::set_mock_now('2018-01-01 12:00:00');
        /** @var PartialSubmissionJob $job */
        $this->job = new PartialSubmissionJob();
        Config::modify()->set(QueuedJobService::class, 'use_shutdown_function', false);
    }

    protected function tearDown()
    {
        parent::tearDown();
    }
}

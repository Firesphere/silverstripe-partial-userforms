<?php

namespace Firesphere\PartialUserforms\Tests;

use Firesphere\PartialUserforms\Tasks\PartialSubmissionTask;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use Symbiote\QueuedJobs\Services\QueuedJobService;

class PartialSubmissionTaskTest extends SapphireTest
{
    public function testTitle()
    {
        $task = Injector::inst()->get(PartialSubmissionTask::class);
        $this->assertEquals('Export partial form submissions to email address', $task->getTitle());
    }

    public function testRun()
    {
        SiteConfigHelper::setupSiteConfig('test@example.com', null, true);
        $request = new HTTPRequest('GET', 'dev/tasks/partialsubmissiontask');

        /** @var PartialSubmissionTask $task */
        $task = Injector::inst()->get(PartialSubmissionTask::class);

        $task->run($request);

        $this->assertEmailSent('test@example.com');
    }

    public function testExtraUser()
    {
        SiteConfigHelper::setupSiteConfig('test@example.com', null, true);
        $user = Member::create(['FirstName' => 'Test', 'Email' => 'userextrauser@example.com']);
        $user->write();
        Security::setCurrentUser($user);
        $request = new HTTPRequest('GET', 'dev/tasks/partialsubmissiontask');

        $task = new PartialSubmissionTask();

        $task->run($request);

        $this->assertEmailSent('userextrauser@example.com');
        $this->assertEmailSent('test@example.com');
    }

    public function testNoConfigButUser()
    {
        $user = Member::create(['FirstName' => 'Test', 'Email' => 'usernoconfig@example.com']);
        $user->write();
        Security::setCurrentUser($user);
        SiteConfigHelper::setupSiteConfig(null, null, true);
        $request = new HTTPRequest('GET', 'dev/tasks/partialsubmissiontask');

        $task = new PartialSubmissionTask();

        $task->run($request);

        $this->assertEmailSent('usernoconfig@example.com');
    }

    protected function setUp()
    {
        $this->usesDatabase = true;
        parent::setUp();
        Config::modify()->set(QueuedJobService::class, 'use_shutdown_function', false);
    }
}

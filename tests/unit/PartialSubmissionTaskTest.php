<?php
/**
 * Created by PhpStorm.
 * User: simon
 * Date: 10-May-18
 * Time: 10:02
 */

namespace Firesphere\PartialUserforms\Tests;

use Firesphere\PartialUserforms\Tasks\PartialSubmissionTask;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use SilverStripe\SiteConfig\SiteConfig;
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
        $config = SiteConfig::current_site_config();
        $config->SendDailyEmail = true;
        $config->SendMailTo = 'test@example.com';
        $config->write();
        $request = new HTTPRequest('GET', 'dev/tasks/partialsubmissiontask');

        $task = Injector::inst()->get(PartialSubmissionTask::class);

        $task->run($request);

        $this->assertEmailSent('test@example.com');
    }

    public function testExtraUser()
    {
        $config = SiteConfig::current_site_config();
        $config->SendDailyEmail = true;
        $config->SendMailTo = 'test@example.com';
        $config->write();
        $user = Member::create(['FirstName' => 'Test', 'Email' => 'user1@example.com']);
        $user->write();
        Security::setCurrentUser($user);
        $request = new HTTPRequest('GET', 'dev/tasks/partialsubmissiontask');

        $task = Injector::inst()->get(PartialSubmissionTask::class);

        $task->run($request);

        $this->assertEmailSent( 'user1@example.com');
        $this->assertEmailSent('test@example.com');
    }

    public function testNoConfigButUser()
    {
        $user = Member::create(['FirstName' => 'Test', 'Email' => 'user1@example.com']);
        $user->write();
        Security::setCurrentUser($user);
        $config = SiteConfig::current_site_config();
        $config->SendDailyEmail = true;
        $config->write();
        $request = new HTTPRequest('GET', 'dev/tasks/partialsubmissiontask');

        $task = Injector::inst()->get(PartialSubmissionTask::class);

        $task->run($request);

        $this->assertEmailSent('user1@example.com');
    }

    protected function setUp()
    {
        parent::setUp();
        Config::modify()->set(QueuedJobService::class, 'use_shutdown_function', false);
    }
}

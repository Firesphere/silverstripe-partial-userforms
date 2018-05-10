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
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;

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
}

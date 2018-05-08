<?php

namespace Firesphere\PartialUserforms\Tests;

use Firesphere\PartialUserforms\Extensions\SiteConfigExtension;
use Firesphere\PartialUserforms\Jobs\PartialSubmissionJob;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Tab;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\SiteConfig\SiteConfig;
use Symbiote\QueuedJobs\DataObjects\QueuedJobDescriptor;
use Symbiote\QueuedJobs\Services\QueuedJobService;

class SiteConfigExtensionTest extends SapphireTest
{
    /**
     * @var SiteConfigExtension
     */
    protected $extension;

    public function testUpdateCMSFields()
    {
        $fieldList = FieldList::create();
        $fieldList->add(Tab::create('Root'));

        $this->extension->updateCMSFields($fieldList);

        $this->assertInstanceOf(CheckboxField::class, $fieldList->dataFieldByName('SendDailyEmail'));
        $this->assertInstanceOf(CheckboxField::class, $fieldList->dataFieldByName('CleanupAfterSend'));
        $this->assertInstanceOf(EmailField::class, $fieldList->dataFieldByName('SendMailTo'));
    }

    public function testOnAfterWriteNoSetting()
    {
        $this->extension->onAfterWrite();

        $this->assertCount(0, QueuedJobDescriptor::get()
            ->filter([
                'Implementation'         => PartialSubmissionJob::class,
                'StartAfter:GreaterThan' => DBDatetime::now()
            ])
            ->column('Implementation'));
    }

    public function testOnAfterWriteSettingNoEmail()
    {
        $config = SiteConfig::current_site_config();
        $config->SendDailyEmail = true;
        $this->extension->setOwner($config);
        $this->extension->onAfterWrite();

        $this->assertCount(0, QueuedJobDescriptor::get()
            ->filter([
                'Implementation'         => PartialSubmissionJob::class,
                'StartAfter:GreaterThan' => DBDatetime::now()
            ])
            ->column('Implementation'));
    }

    public function testOnAfterWriteCreateJob()
    {
        $config = SiteConfig::current_site_config();
        $config->SendDailyEmail = true;
        $config->SendMailTo = 'test@example.com';
        $this->extension->setOwner($config);
        $this->extension->onAfterWrite();

        $jobs = QueuedJobDescriptor::get()
            ->filter([
                'Implementation'         => PartialSubmissionJob::class,
                'StartAfter:GreaterThan' => DBDatetime::now()
            ])
            ->column('Implementation');

        $this->assertCount(1, $jobs);
    }

    public function testOnAfterWriteNoDuplicateJob()
    {
        $job = Injector::inst()->get(PartialSubmissionJob::class);
        /** @var QueuedJobService $queuedJob */
        $queuedJob = Injector::inst()->get(QueuedJobService::class);
        $tomorrow = date('Y-m-d H:i:s', strtotime('+1 day'));
        $queuedJob->queueJob($job, $tomorrow);

        $config = SiteConfig::current_site_config();
        $config->SendDailyEmail = true;
        $config->SendMailTo = 'test@example.com';
        $this->extension->setOwner($config);
        $this->extension->onAfterWrite();

        $jobs = QueuedJobDescriptor::get()
            ->filter([
                'Implementation'         => PartialSubmissionJob::class,
                'StartAfter:GreaterThan' => DBDatetime::now()
            ])
            ->column('Implementation');

        $this->assertCount(1, $jobs);
    }

    protected function setUp()
    {
        /** @var SiteConfigExtension $extension */
        $extension = Injector::inst()->get(SiteConfigExtension::class);
        $extension->setOwner(SiteConfig::current_site_config());
        $this->extension = $extension;

        return parent::setUp();
    }

    protected function tearDown()
    {
        /** DataList */
        QueuedJobDescriptor::get()
            ->filter([
                'Implementation'         => PartialSubmissionJob::class,
                'StartAfter:GreaterThan' => DBDatetime::now()
            ])->removeAll();

        parent::tearDown();
    }
}

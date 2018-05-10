<?php

namespace Firesphere\PartialUserforms\Extensions;

use DateInterval;
use DateTime;
use Firesphere\PartialUserforms\Jobs\PartialSubmissionJob;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\FieldType\DBDatetime;
use Symbiote\QueuedJobs\DataObjects\QueuedJobDescriptor;
use Symbiote\QueuedJobs\Services\QueuedJobService;

/**
 * Class SiteConfigExtension
 *
 * @package Firesphere\PartialUserforms\Extensions
 * @property \SilverStripe\SiteConfig\SiteConfig|\Firesphere\PartialUserforms\Extensions\SiteConfigExtension $owner
 * @property boolean $SendDailyEmail
 * @property boolean $CleanupAfterSend
 * @property string $SendMailTo
 */
class SiteConfigExtension extends DataExtension
{
    /**
     * @var array
     */
    private static $db = [
        'SendDailyEmail'   => 'Boolean(false)',
        'CleanupAfterSend' => 'Boolean(false)',
        'SendMailTo'       => 'Varchar(2550'
    ];

    /**
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldToTab('Root', Tab::create('PartialUserFormSubmissions'));

        $fields->addFieldsToTab('Root.PartialUserFormSubmissions', [
            CheckboxField::create(
                'SendDailyEmail',
                _t(__CLASS__ . 'SendDailyEmail', 'Send partial submissions daily')
            ),
            CheckboxField::create(
                'CleanupAfterSend',
                _t(__CLASS__ . 'CleanupAfterSend', 'Remove partial submissions after sending')
            ),
            $emailField = TextField::create(
                'SendMailTo',
                _t(__CLASS__ . 'SendMailTo', 'Email address the partial submissions should be send to')
            )
        ]);

        $emailField->setDescription(_t(__CLASS__ . '.EmailDescription', 'Can be a comma separated set of addresses'));
    }

    /**
     * @throws \Exception
     */
    public function onAfterWrite()
    {
        parent::onAfterWrite();

        if ($this->owner->SendDailyEmail && !empty($this->owner->SendMailTo)) {
            $jobs = QueuedJobDescriptor::get()->filter([
                'Implementation'         => PartialSubmissionJob::class,
                'StartAfter:GreaterThan' => DBDatetime::now()
            ]);
            // Only create a new job if there isn't one already
            if ((int)$jobs->count() === 0) {
                $job = Injector::inst()->get(PartialSubmissionJob::class);
                /** @var QueuedJobService $queuedJob */
                $queuedJob = Injector::inst()->get(QueuedJobService::class);
                $dbDateTime = $this->getTomorrow();
                $queuedJob->queueJob($job, $dbDateTime->Format(DBDatetime::ISO_DATETIME));
            }
        }
    }

    /**
     * @return DBDatetime|static
     * @throws \Exception
     */
    protected function getTomorrow()
    {
        $dateTime = new DateTime(DBDatetime::now());
        $interval = new DateInterval('P1D');
        $tomorrow = $dateTime->add($interval);
        $dbDateTime = DBDatetime::create();
        $dbDateTime->setValue($tomorrow->format('Y-m-d 00:00:00'));

        return $dbDateTime;
    }
}

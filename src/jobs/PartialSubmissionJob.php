<?php

namespace Firesphere\PartialUserforms\Jobs;

use DateInterval;
use DateTime;
use DNADesign\ElementalUserForms\Model\ElementForm;
use Firesphere\PartialUserforms\Models\PartialFieldSubmission;
use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use SilverStripe\Control\Email\Email;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\Debug;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\UserForms\Model\UserDefinedForm;
use Symbiote\QueuedJobs\Services\AbstractQueuedJob;
use Symbiote\QueuedJobs\Services\QueuedJobService;

/**
 * Class PartialSubmissionJob
 * @package Firesphere\PartialUserforms\Jobs
 */
class PartialSubmissionJob extends AbstractQueuedJob
{

    /**
     * The generated CSV files
     * @var array
     */
    protected $files = [];

    /**
     * @var SiteConfig
     */
    protected $config;

    /**
     * @var array
     */
    protected $addresses;


    /**
     * @return string
     */
    public function getTitle()
    {
        return _t(__CLASS__ . '.Title', 'Export partial submissions to Email');
    }

    /**
     * Do some processing yourself!
     */
    public function process()
    {
        $this->config = SiteConfig::current_site_config();
        Debug::dump($this->config);
        $this->validateEmails();

        if (!$this->config->SendDailyEmail ||
            !count($this->addresses)
        ) {
            $this->addMessage(_t(__CLASS__ . '.EmailError', 'Can not process without valid email'));
            $this->isComplete = true;

            return;
        }

        $userDefinedForms = $this->getParents();

        /** @var UserDefinedForm $form */
        foreach ($userDefinedForms as $form) {
            $fileName = _t(__CLASS__ . '.Export', 'Export of ') .
                $form->Title . ' - ' .
                DBDatetime::now()->Format(DBDatetime::ISO_DATETIME);
            $file = '/tmp/' . $fileName . '.csv';
            $this->files[] = $file;
            $this->buildCSV($file, $form);
        }

        $this->sendEmail();


        $this->isComplete = true;
    }

    /**
     * Only add valid email addresses
     */
    protected function validateEmails()
    {
        $email = $this->config->SendMailTo;
        $result = Email::is_valid_address($email);
        if ($result) {
            $this->addresses[] = $email;
        }
        if (strpos($email, ',') !== false) {
            $emails = explode(',', $email);
            foreach ($emails as $address) {
                $result = Email::is_valid_address(trim($address));
                if ($result) {
                    $this->addresses[] = trim($address);
                } else {
                    $this->addMessage($address . _t(__CLASS__ . '.invalidMail', ' is not a valid email address'));
                }
            }
        }
    }

    /**
     * @return ArrayList
     */
    protected function getParents()
    {
        /** @var DataList|PartialFormSubmission[] $exportForms */
        $allSubmissions = PartialFormSubmission::get()->filter(['IsSend' => false]);
        /** @var ArrayList|UserDefinedForm[]|ElementForm[] $parents */
        $userDefinedForms = ArrayList::create();

        /** @var PartialFormSubmission $submission */
        /** @noinspection ForeachSourceInspection */
        foreach ($allSubmissions as $submission) {
            // Due to having to support Elemental ElementForm, we need to manually get the parent
            // It's a bit a pickle, but it works
            $parentClass = $submission->ParentClass;
            $parent = $parentClass::get()->byID($submission->UserDefinedFormID);
            if ($parent &&
                $parent->ExportPartialSubmissions &&
                !$userDefinedForms->find('ID', $parent->ID)
            ) {
                $userDefinedForms->push($parent);
            }
            $submission->destroy();
        }

        return $userDefinedForms;
    }

    /**
     * @param $file
     * @param $form
     */
    protected function buildCSV($file, $form)
    {
        $resource = fopen($file, 'w+');
        /** @var PartialFormSubmission $submissions */
        $submissions = PartialFormSubmission::get()->filter(['UserDefinedFormID' => $form->ID]);
        $headerFields = $form
            ->Fields()
            ->exclude(['Name:PartialMatch' => 'EditableFormStep'])
            ->column('Title');
        fputcsv($resource, $headerFields);

        if ($submissions->count()) {
            $this->processSubmissions($form, $submissions, $resource);
        }
        fclose($resource);
    }

    /**
     * @param $form
     * @param $submissions
     * @param $resource
     */
    protected function processSubmissions($form, $submissions, $resource)
    {
        $editableFields = $form->Fields()->map('Name', 'Title')->toArray();
        $submitted = [];
        foreach ($submissions as $submission) {
            $values = $submission->PartialFields()->map('Name', 'Value')->toArray();
            $i = 0;
            foreach ($editableFields as $field => $title) {
                $submitted[] = '';
                if (isset($values[$field])) {
                    $submitted[] = $values[$field];
                }
                $i++;
            }
            fputcsv($resource, $submitted);
            $submission->IsSend = true;
            $submission->write();
        }
    }

    /**
     * @throws \Exception
     */
    public function afterComplete()
    {
        // Remove the files created in the process
        foreach ($this->files as $file) {
            unlink($file);
        }

        parent::afterComplete();
        if ($this->config->CleanupAfterSend) {
            $this->cleanupSubmissions();
        }
        if ($this->config->SendDailyEmail) {
            $this->createNewJob();
        }
    }

    /**
     * Remove submissions that have been sent out
     */
    protected function cleanupSubmissions()
    {
        /** @var DataList|PartialFormSubmission[] $forms */
        $forms = PartialFormSubmission::get()->filter(['IsSend' => true]);
        foreach ($forms as $form) {
            /** @var DataList|PartialFieldSubmission[] $fields */
            $fields = PartialFieldSubmission::get()->filter(['ID' => $form->PartialFields()->column('ID')]);
            $fields->removeAll();
            $form->delete();
            $form->destroy();
        }
    }

    /**
     * Create a new queued job for tomorrow
     * @throws \Exception
     */
    protected function createNewJob()
    {
        $job = new self();
        /** @var QueuedJobService $queuedJob */
        $queuedJob = Injector::inst()->get(QueuedJobService::class);
        $tomorrow = $this->getTomorrow();
        $queuedJob->queueJob($job, $tomorrow);
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

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @return array
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    public function addAddress($address)
    {
        if (Email::is_valid_address($address)) {
            $this->addresses[] = $address;
        }
    }

    protected function sendEmail()
    {
        /** @var Email $mail */
        $mail = Email::create();
        $mail->setSubject('Partial form submissions of ' . DBDatetime::now()->Format(DBDatetime::ISO_DATETIME));
        foreach ($this->files as $file) {
            $mail->addAttachment($file);
        }

        $mail->setFrom('test@example.com');
        $mail->setTo($this->addresses);
        $mail->setBody('Please see attached CSV files');
    }
}

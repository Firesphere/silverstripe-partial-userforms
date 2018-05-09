<?php

namespace Firesphere\PartialUserforms\Jobs;

use SilverStripe\Control\Email\Email;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\UserForms\Model\UserDefinedForm;
use Symbiote\QueuedJobs\Services\AbstractQueuedJob;

class PartialSubmissionJob extends AbstractQueuedJob
{

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
        $config = SiteConfig::current_site_config();
        /** @var DataList|UserDefinedForm[] $exportForms */
        $exportForms = UserDefinedForm::get()->filter(['ExportPartialSubmissions' => true]);
        $files = [];
        foreach ($exportForms as $form) {
            // @todo generate a CSV for each form that should be exported;
        }

        /** @var Email $mail */
        $mail = Email::create();

        $mail->setSubject('Partial form submissions of ' . DBDatetime::now()->Format(DBDatetime::ISO_DATETIME));
        foreach ($files as $file) {
            $mail->addAttachment($file);
        }
        $mail->setTo($config->SendMailTo);
        $mail->send();
    }
}

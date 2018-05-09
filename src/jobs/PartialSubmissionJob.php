<?php

namespace Firesphere\PartialUserforms\Jobs;

use SilverStripe\ORM\DataList;
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
        /** @var DataList|UserDefinedForm[] $exportForms */
        $exportForms = UserDefinedForm::get()->filter(['ExportPartialSubmissions' => true]);
        foreach ($exportForms as $form) {
            // @todo generate a CSV for each form that should be exported;
        }
    }
}

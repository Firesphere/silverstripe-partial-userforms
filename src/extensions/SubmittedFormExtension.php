<?php

namespace Firesphere\PartialUserforms\Extensions;

use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use SilverStripe\Control\Controller;
use SilverStripe\ORM\DataExtension;

class SubmittedFormExtension extends DataExtension
{

    public function updateAfterProcess()
    {
        // cleanup partial submissions
        $partialID = Controller::curr()->getRequest()->getSession()->get('PartialSubmissionID');
        /** @var PartialFormSubmission $partialForm */
        $partialForm = PartialFormSubmission::get()->byID($partialID);
        foreach ($partialForm->PartialFields() as $field) {
            $field->delete();
            $field->destroy();
        }
        $partialForm->delete();
        $partialForm->destroy();
    }
}
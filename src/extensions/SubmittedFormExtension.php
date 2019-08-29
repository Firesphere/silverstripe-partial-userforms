<?php

namespace Firesphere\PartialUserforms\Extensions;

use Firesphere\PartialUserforms\Controllers\PartialSubmissionController;
use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use SilverStripe\Control\Controller;
use SilverStripe\ORM\DataExtension;
use SilverStripe\UserForms\Model\Submission\SubmittedForm;

/**
 * Class \Firesphere\PartialUserforms\Extensions\SubmittedFormExtension
 *
 * @property SubmittedForm|SubmittedFormExtension $owner
 */
class SubmittedFormExtension extends DataExtension
{

    /**
     * Remove the partial submissions after completion
     */
    public function updateAfterProcess()
    {
        // cleanup partial submissions
        $partialID = Controller::curr()->getRequest()->getSession()->get(PartialSubmissionController::SESSION_KEY);
        if ($partialID === null) {
            return;
        }

        /** @var PartialFormSubmission $partialForm */
        $partialForm = PartialFormSubmission::get()->byID($partialID);
        if ($partialForm === null) {
            return;
        }

        $partialForm->delete();
        $partialForm->destroy();
        Controller::curr()->getRequest()->getSession()->clear(PartialSubmissionController::SESSION_KEY);
    }
}

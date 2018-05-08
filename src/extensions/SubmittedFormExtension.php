<?php

namespace Firesphere\PartialUserforms\Extensions;

use Firesphere\PartialUserforms\Controllers\PartialUserFormController;
use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use SilverStripe\Control\Controller;
use SilverStripe\ORM\DataExtension;

/**
 * Class \Firesphere\PartialUserforms\Extensions\SubmittedFormExtension
 *
 * @property \SilverStripe\UserForms\Model\Submission\SubmittedForm|\Firesphere\PartialUserforms\Extensions\SubmittedFormExtension $owner
 */
class SubmittedFormExtension extends DataExtension
{
    public function updateAfterProcess()
    {
        // cleanup partial submissions
        $partialID = Controller::curr()->getRequest()->getSession()->get(PartialUserFormController::SESSION_KEY);
        /** @var PartialFormSubmission $partialForm */
        $partialForm = PartialFormSubmission::get()->byID($partialID);
        foreach ($partialForm->PartialFields() as $field) {
            $field->delete();
            $field->destroy();
        }
        $partialForm->delete();
        $partialForm->destroy();
        Controller::curr()->getRequest()->getSession()->clear(PartialUserFormController::SESSION_KEY);
    }
}

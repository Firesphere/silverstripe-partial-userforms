<?php

namespace Firesphere\PartialUserforms\Extensions;

use Firesphere\PartialUserforms\Controllers\PartialUserFormController;
use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use SilverStripe\Control\Controller;
use SilverStripe\ORM\DataExtension;

/**
 * Class \Firesphere\PartialUserforms\Extensions\SubmittedFormExtension
 *
 * @property SubmittedForm|SubmittedFormExtension $owner
 */
class SubmittedFormExtension extends DataExtension
{
    /**
     * @var array
     */
    private static $summary_fields = [
        'IsPartial' => 'IsPartial'
    ];

    /**
     * Remove the partial submissions after completion
     */
    public function updateAfterProcess()
    {
        // cleanup partial submissions
        $partialID = Controller::curr()->getRequest()->getSession()->get(PartialUserFormController::SESSION_KEY);
        /** @var PartialFormSubmission $partialForm */
        $partialForm = PartialFormSubmission::get()->byID($partialID);
        if ($partialForm) {
            foreach ($partialForm->PartialFields() as $field) {
                $field->delete();
                $field->destroy();
            }
            $partialForm->delete();
            $partialForm->destroy();
        }
        Controller::curr()->getRequest()->getSession()->clear(PartialUserFormController::SESSION_KEY);
    }

    /**
     * Is it a partial submission or not
     *
     * @return string
     */
    public function getIsPartial()
    {
        if ($this->owner->ClassName === PartialFormSubmission::class) {
            return _t(__CLASS__ . '.partial', 'Partial submission');
        }

        return _t(__CLASS__ . '.notPartial', 'Completed submission');
    }
}

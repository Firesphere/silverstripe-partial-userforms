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
        $request = Controller::curr()->getRequest();

        $postID = $request->postVar('PartialID');
        $partialID = $postID ?? $request->getSession()->get(PartialSubmissionController::SESSION_KEY);
        if ($partialID === null) {
            return;
        }

        /** @var PartialFormSubmission $partialForm */
        $partialForm = PartialFormSubmission::get()->byID($partialID);
        if ($partialForm === null) {
            return;
        }

        // Link files to SubmittedForm
        $uploads = $partialForm->PartialUploads()->filter([
            'UploadedFileID:not'=> 0
        ]);
        if ($uploads->exists()) {
            foreach ($uploads as $upload) {
                $upload->ParentID = $this->owner->ID;
                $upload->write();
            }
        }

        $partialForm->delete();
        $partialForm->destroy();
        $request->getSession()->clear(PartialSubmissionController::SESSION_KEY);
    }
}

<?php

namespace Firesphere\PartialUserforms\Extensions;

use Firesphere\PartialUserforms\Controllers\PartialSubmissionController;
use Firesphere\PartialUserforms\Models\PartialFieldSubmission;
use Firesphere\PartialUserforms\Models\PartialFileFieldSubmission;
use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use SilverStripe\Control\NullHTTPRequest;
use SilverStripe\Core\Extension;
use SilverStripe\UserForms\Control\UserDefinedFormController;
use SilverStripe\UserForms\Model\EditableFormField\EditableFileField;
use SilverStripe\UserForms\Model\EditableFormField\EditableFormStep;
use SilverStripe\View\Requirements;

/**
 * Class UserDefinedFormControllerExtension
 *
 * @package Firesphere\PartialUserforms\Extensions
 * @property UserDefinedFormController|UserDefinedFormControllerExtension $owner
 */
class UserDefinedFormControllerExtension extends Extension
{
    /**
     * Add required javascripts
     */
    public function onBeforeInit()
    {
        Requirements::javascript('firesphere/partialuserforms:client/dist/main.js');
    }

    /**
     * Start a clean session if the user visits the original form
     */
    public function onAfterInit()
    {
        $request = $this->owner->getRequest();
        if ($request instanceof NullHTTPRequest) {
            return;
        }

        $params = $this->owner->getRequest()->params();
        // Pages without action e.g. /partial
        if (!array_key_exists('Action', $params)) {
            return;
        }

        // This should only run on index
        if ($params['Action'] === null || $params['Action'] === 'index') {
            $session = $this->owner->getRequest()->getSession();
            if (!$session) {
                return;
            }

            $this->createPartialSubmission();
        }
    }

    /**
     * Creates a new partial submission and partial fields.
     *
     * @throws \SilverStripe\ORM\ValidationException
     */
    protected function createPartialSubmission()
    {
        $page = $this->owner;
        // Create partial form
        $partialSubmission = PartialFormSubmission::create([
            'ParentID'              => $page->ID,
            'ParentClass'           => $page->ClassName,
            'UserDefinedFormID'     => $page->ID,
            'UserDefinedFormClass'  => $page->ClassName,
        ]);
        $submissionID = $partialSubmission->write();

        // Create partial fields
        foreach ($page->data()->Fields() as $field) {

            // We don't need literal fields, headers, html, etc
            if ($field::config()->literal === true || $field->ClassName == EditableFormStep::class) {
                continue;
            }

            $newData = [
                'SubmittedFormID'   => $submissionID,
                'Name'              => $field->Name,
                'Title'             => $field->getField('Title'),
            ];

            if (in_array(EditableFileField::class, $field->getClassAncestry())) {
                $partialFile = PartialFileFieldSubmission::create($newData);
                $partialSubmission->PartialUploads()->add($partialFile);
            } else {
                $partialField = PartialFieldSubmission::create($newData);
                $partialSubmission->PartialFields()->add($partialField);
            }
        }

        // Refresh session on start
        $page->getRequest()->getSession()->set(PartialSubmissionController::SESSION_KEY, $submissionID);
    }
}

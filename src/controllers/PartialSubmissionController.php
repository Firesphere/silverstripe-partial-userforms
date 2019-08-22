<?php

namespace Firesphere\PartialUserforms\Controllers;

use Firesphere\PartialUserforms\Models\PartialFieldSubmission;
use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use SilverStripe\CMS\Controllers\ContentController;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ValidationException;
use SilverStripe\UserForms\Model\EditableFormField;

/**
 * Class PartialSubmissionController
 * @package Firesphere\PartialUserforms\Controllers
 */
class PartialSubmissionController extends ContentController
{
    /**
     * Session key name
     */
    const SESSION_KEY = 'PartialSubmissionID';

    /**
     * @var array
     */
    private static $url_handlers = [
        'save' => 'savePartialSubmission',
    ];

    /**
     * @var array
     */
    private static $allowed_actions = [
        'savePartialSubmission',
    ];

    /**
     * @param HTTPRequest $request
     * @return bool|void
     * @throws ValidationException
     * @throws \SilverStripe\Control\HTTPResponse_Exception
     */
    public function savePartialSubmission(HTTPRequest $request)
    {
        if (!$request->isPOST()) {
            return $this->httpError(404);
        }

        $postVars = $request->postVars();
        $editableField = null;

        // We don't want SecurityID and/or the process Action stored as a thing
        unset($postVars['SecurityID'], $postVars['action_process']);
        $submissionID = $request->getSession()->get(self::SESSION_KEY);

        /** @var PartialFormSubmission $partialSubmission */
        $partialSubmission = PartialFormSubmission::get()->byID($submissionID);

        if (!$submissionID || !$partialSubmission) {
            $partialSubmission = PartialFormSubmission::create();
            // TODO: Set the Parent ID and Parent Class before write, this issue will create new submissions
            // every time the session expires when the user proceeds to the next step.
            // Also, saving a new submission without a parent creates an "AccordionItems" as parent class (first DataObject found)
            $submissionID = $partialSubmission->write();
        }
        $request->getSession()->set(self::SESSION_KEY, $submissionID);
        foreach ($postVars as $field => $value) {
            /** @var EditableFormField $editableField */
            $editableField = $this->createOrUpdateSubmission([
                'Name'            => $field,
                'Value'           => $value,
                'SubmittedFormID' => $submissionID
            ]);
        }

        if ($editableField instanceof EditableFormField && !$partialSubmission->UserDefinedFormID) {
            // Updates parent class to the correct DataObject
            $partialSubmission->update([
                'UserDefinedFormID'    => $editableField->Parent()->ID,
                'ParentID'             => $editableField->Parent()->ID,
                'ParentClass'          => $editableField->Parent()->ClassName,
                'UserDefinedFormClass' => $editableField->Parent()->ClassName
            ]);
            $partialSubmission->write();
        }

        return $partialSubmission->exists();
    }

    /**
     * @param $formData
     * @return DataObject|EditableFormField
     * @throws ValidationException
     */
    protected function createOrUpdateSubmission($formData)
    {
        if (is_array($formData['Value'])) {
            $formData['Value'] = implode(', ', $formData['Value']);
        }

        $filter = [
            'Name'            => $formData['Name'],
            'SubmittedFormID' => $formData['SubmittedFormID'],
        ];

        $exists = PartialFieldSubmission::get()->filter($filter)->first();
        // Set the title
        $editableField = EditableFormField::get()->filter(['Name' => $formData['Name']])->first();
        if ($editableField) {
            $formData['Title'] = $editableField->Title;
            $formData['ParentClass'] = $editableField->Parent()->ClassName;
        }
        if (!$exists) {
            $exists = PartialFieldSubmission::create($formData);
            $exists->write();
        } else {
            $exists->update($formData);
            $exists->write();
        }

        // Return the ParentID to link the PartialSubmission to it's proper thingy
        return $editableField;
    }
}

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
 * Class \Firesphere\PartialUserforms\Controllers\PartialUserFormController
 *
 */
class PartialUserFormController extends ContentController
{
    const SESSION_KEY = 'PartialSubmissionID';

    private static $url_handlers = [
        '' => 'savePartialSubmission'
    ];

    private static $allowed_actions = [
        'savePartialSubmission'
    ];

    /**
     * @param HTTPRequest $request
     * @return int
     * @throws ValidationException
     */
    public function savePartialSubmission(HTTPRequest $request)
    {
        $postVars = $request->postVars();

        // We don't want SecurityID and/or the process Action stored as a thing
        unset($postVars['SecurityID'], $postVars['action_process']);
        $submissionID = $request->getSession()->get(self::SESSION_KEY);

        /** @var PartialFormSubmission $partialSubmission */
        $partialSubmission = PartialFormSubmission::get()->byID($submissionID);

        if (!$submissionID || !$partialSubmission) {
            $partialSubmission = PartialFormSubmission::create();
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

        if (!$partialSubmission->ParentID) {
            $partialSubmission->update([
                'ParentID'    => $editableField->Parent()->ParentID,
                'ParentClass' => $editableField->Parent()->ClassName
            ])->write();
        }

        return $submissionID;
    }

    /**
     * @param $formData
     * @return DataObject|EditableFormField
     * @throws ValidationException
     */
    protected function createOrUpdateSubmission($formData)
    {
        $filter = [
            'Name'            => $formData['Name'],
            'SubmittedFormID' => $formData['SubmittedFormID'],
        ];
        $exists = PartialFieldSubmission::get()->filter($filter)->first();
        // Set the title
        $editableField = EditableFormField::get()->filter(['Name' => $formData['Name']])->first();
        $formData['Title'] = $editableField->Title;
        $formData['ParentClass'] = $editableField->Parent()->ClassName;
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

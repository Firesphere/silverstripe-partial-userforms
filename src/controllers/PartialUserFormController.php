<?php

namespace Firesphere\PartialUserforms\Controllers;

use Firesphere\PartialUserforms\Models\PartialFieldSubmission;
use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use SilverStripe\CMS\Controllers\ContentController;
use SilverStripe\Control\HTTPRequest;
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
     * @throws \SilverStripe\ORM\ValidationException
     */
    public function savePartialSubmission(HTTPRequest $request)
    {
        $postVars = $request->postVars();

        $submissionID = $request->getSession()->get(self::SESSION_KEY);
        if (!$submissionID) {
            $partialSubmission = PartialFormSubmission::create();
            $submissionID = $partialSubmission->write();
        }
        $request->getSession()->set(self::SESSION_KEY, $submissionID);
        foreach ($postVars as $field => $value) {
            $this->createOrUpdateSubmission([
                'Name'            => $field,
                'Value'           => $value,
                'SubmittedFormID' => $submissionID
            ]);
        }

        return $submissionID;
    }

    /**
     * @param $formData
     * @throws \SilverStripe\ORM\ValidationException
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

        if (!$exists) {
            $field = PartialFieldSubmission::create($formData);
            $field->write();
        } else {
            $exists->update($formData);
            $exists->write();
        }
    }
}

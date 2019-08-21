<?php

namespace Firesphere\PartialUserforms\Controllers;

use Firesphere\PartialUserforms\Models\PartialFieldSubmission;
use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use SilverStripe\CMS\Controllers\ContentController;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ValidationException;
use SilverStripe\UserForms\Control\UserDefinedFormController;
use SilverStripe\UserForms\Model\EditableFormField;
use SilverStripe\View\Requirements;

/**
 * Class PartialUserFormController
 * @package Firesphere\PartialUserforms\Controllers
 */
class PartialUserFormController extends ContentController
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
        '$Key/$Token' => 'partial',
    ];

    /**
     * @var array
     */
    private static $allowed_actions = [
        'savePartialSubmission',
        'partial',
    ];

    /**
     * @param HTTPRequest $request
     * @return int|mixed|void
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
            $partialSubmission->update([
                'UserDefinedFormID'    => $editableField->Parent()->ID,
                'ParentID'             => $editableField->Parent()->ID,
                'ParentClass'          => $editableField->Parent()->ClassName,
                'UserDefinedFormClass' => $editableField->Parent()->ClassName
            ]);
            $partialSubmission->write();
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

    /**
     * Partial form
     *
     * @param HTTPRequest $request
     * @return \SilverStripe\ORM\FieldType\DBHTMLText|void
     * @throws \SilverStripe\Control\HTTPResponse_Exception
     */
    public function partial(HTTPRequest $request)
    {
        $key = $request->param('Key');
        $token = $request->param('Token');

        $partial = PartialFormSubmission::get()->find('Token', $token);
        if (!$token || !$partial || !$partial->UserDefinedFormID) {
            return $this->httpError(404);
        }

        if ($partial->generateKey($token) === $key) {
            // Set the session if the last session has expired
            if (!$request->getSession()->get(self::SESSION_KEY)) {
                $request->getSession()->set(self::SESSION_KEY, $partial->ID);
            }

            // TODO: Recognize visitor with the password
            // TODO: Populate form values

            $record = DataObject::get_by_id($partial->UserDefinedFormClass, $partial->UserDefinedFormID);
            $controller = new UserDefinedFormController($record);
            $controller->init();

            Requirements::javascript('firesphere/partialuserforms:client/dist/main.js');

            return $this->customise([
                'Title' => $record->Title,
                'Breadcrumbs' => $record->Breadcrumbs(),
                'Content' => $this->obj('Content'),
                'Form' => $controller->Form(),
                'Link' => $partial->getPartialLink()
            ])->renderWith(['PartialUserForm', 'Page']);
        } else {
            return $this->httpError(404);
        }
    }
}

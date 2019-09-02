<?php

namespace Firesphere\PartialUserforms\Controllers;

use Exception;
use Firesphere\PartialUserforms\Models\PartialFieldSubmission;
use Firesphere\PartialUserforms\Models\PartialFileFieldSubmission;
use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Upload;
use SilverStripe\CMS\Controllers\ContentController;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ValidationException;
use SilverStripe\UserForms\Model\EditableFormField;

/**
 * Class PartialSubmissionController
 *
 * @package Firesphere\PartialUserforms\Controllers
 */
class PartialSubmissionController extends ContentController
{
    /**
     * Session key name
     */
    public const SESSION_KEY = 'PartialSubmissionID';

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
     * @return HTTPResponse
     * @throws ValidationException
     * @throws HTTPResponse_Exception
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
            // Also, saving a new submission without a parent creates an
            // "AccordionItems" as parent class (first DataObject found)
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

        $return = $partialSubmission->exists();

        return new HTTPResponse($return, ($return ? 201 : 400));
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

        /** @var EditableFormField $editableField */
        $editableField = EditableFormField::get()->filter(['Name' => $formData['Name']])->first();
        if ($editableField instanceof EditableFormField\EditableFileField) {
            $this->savePartialFile($formData, $filter, $editableField);
        } elseif ($editableField instanceof EditableFormField) {
            $this->savePartialField($formData, $filter, $editableField);
        }

        // Return the ParentID to link the PartialSubmission to it's proper thingy
        return $editableField;
    }

    /**
     * @param $formData
     * @param array $filter
     * @param EditableFormField $editableField
     * @throws ValidationException
     */
    protected function savePartialField($formData, array $filter, EditableFormField $editableField)
    {
        $partialSubmission = PartialFieldSubmission::get()->filter($filter)->first();
        if (is_array($formData['Value'])) {
            $formData['Value'] = implode(', ', $formData['Value']);
        }
        if ($editableField) {
            $formData['Title'] = $editableField->Title;
            $formData['ParentClass'] = $editableField->Parent()->ClassName;
        }
        if (!$partialSubmission) {
            $partialSubmission = PartialFieldSubmission::create($formData);
        } else {
            $partialSubmission->update($formData);
        }
        $partialSubmission->write();
    }

    /**
     * @param $formData
     * @param array $filter
     * @param EditableFormField\EditableFileField $editableField
     * @throws ValidationException
     * @throws Exception
     */
    protected function savePartialFile($formData, array $filter, EditableFormField\EditableFileField $editableField)
    {
        $partialFileSubmission = PartialFileFieldSubmission::get()->filter($filter)->first();
        if (!$partialFileSubmission && $editableField) {
            $partialData = [
                'Name'            => $formData['Name'],
                'SubmittedFormID' => $formData['SubmittedFormID'],
                'Title'           => $editableField->Title,
                'ParentClass'     => $editableField->Parent()->ClassName
            ];
            $partialFileSubmission = PartialFileFieldSubmission::create($partialData);
            $partialFileSubmission->write();
        }

        if (is_array($formData['Value'])) {
            $file = $this->uploadFile($formData, $editableField, $partialFileSubmission);
            $partialFileSubmission->UploadedFileID = $file->ID ?? 0;
            $partialFileSubmission->write();
        }
    }

    /**
     * @param array $formData
     * @param EditableFormField\EditableFileField $field
     * @param PartialFileFieldSubmission $partialFileSubmission
     * @return bool|File
     * @throws Exception
     */
    protected function uploadFile($formData, $field, $partialFileSubmission)
    {
        if (!empty($formData['Value']['name'])) {
            $foldername = $field->getFormField()->getFolderName();

            if (!$partialFileSubmission->UploadedFileID) {
                $file = File::create([
                    'ShowInSearch' => 0
                ]);
            } else {
                // Allow overwrite existing uploads
                $file = $partialFileSubmission->UploadedFile();
            }

            // Upload the file from post data
            $upload = Upload::create();
            if ($upload->loadIntoFile($formData['Value'], $file, $foldername)) {
                return $file;
            }
        }

        return false;
    }
}

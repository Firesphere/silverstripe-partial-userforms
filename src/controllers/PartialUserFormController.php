<?php

namespace Firesphere\PartialUserforms\Controllers;

use Firesphere\PartialUserforms\Models\PartialFieldSubmission;
use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use SilverStripe\CMS\Controllers\ContentController;
use SilverStripe\Control\HTTPRequest;

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
                'Name'     => $field,
                'Value'    => $value,
                'ParentID' => $submissionID
            ]);
        }

        return $submissionID;
    }

    protected function createOrUpdateSubmission($formData)
    {
        $filter = $formData;
        unset($filter['Value']);
        $exists = PartialFieldSubmission::get()->filter($filter)->first();
        if (!$exists) {
            PartialFieldSubmission::create($formData)->write();
        } else {
            $exists->update($formData)->write();
        }
    }
}
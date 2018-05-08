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
        '*' => 'savePartialSubmission'
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
            PartialFieldSubmission::create([
                'Name'     => $field,
                'Value'    => $value,
                'ParentID' => $submissionID
            ])->write();
        }

        return $submissionID;
    }
}
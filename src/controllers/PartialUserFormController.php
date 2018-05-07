<?php

namespace Firesphere\PartialUserforms\Controllers;

use Firesphere\PartialUserforms\Models\PartialFieldSubmission;
use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use SilverStripe\CMS\Controllers\ContentController;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\Debug;

class PartialUserFormController extends ContentController
{

    private static $url_handlers = [
        '*' => 'savePartialSubmission'
    ];

    public function savePartialSubmission(HTTPRequest $request)
    {
        $postVars = $request->postVars();
        $partialSubmission = PartialFormSubmission::create();
        $partialSubmission->write();

        Debug::dump($postVars);
        foreach ($postVars as $field => $value) {
            PartialFieldSubmission::create([
                'Name'     => $field,
                'Value'    => $value,
                'ParentID' => $partialSubmission->ID
            ])->write();
        }

        return $partialSubmission->ID;
    }
}
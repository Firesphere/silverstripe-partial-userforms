<?php

namespace Firesphere\PartialUserforms\Extensions;

use Firesphere\PartialUserforms\Controllers\PartialSubmissionController;
use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use SilverStripe\Core\Extension;
use SilverStripe\UserForms\Control\UserDefinedFormController;
use SilverStripe\View\Requirements;

/**
 * Class UserDefinedFormControllerExtension
 *
 * @package Firesphere\PartialUserforms\Extensions
 * @property UserDefinedFormController|UserDefinedFormControllerExtension $owner
 */
class UserDefinedFormControllerExtension extends Extension
{
    private static $allowed_actions = [
        'verify'
    ];

    /**
     * Add required javascripts
     */
    public function onBeforeInit()
    {
        /** @var UserDefinedFormController $owner */
        $owner = $this->owner;
        $url = $owner->getRequest()->getURL();
        // Start a clean session if the user visits the original form
        if (strpos($url, 'partial') !== 0) {
            $existing = null;
            $started = false;
            $session = $owner->getRequest()->getSession();
            $id = $session->get(PartialSubmissionController::SESSION_KEY);
            // Check if there is an existing partial submission
            if ($id) {
                $existing = PartialFormSubmission::get()->byID($id);
                if ($existing) {
                    // Check if it has started yet
                    $started = $existing->isStarted();
                }
            }
            // If there is an existing one that has not started, or if there is none, start a new submission
            if ($started || !$existing) {
                $partialForm = PartialFormSubmission::create()->write();
                $session->set(PartialSubmissionController::SESSION_KEY, $partialForm);
            }
        }

        Requirements::javascript('firesphere/partialuserforms:client/dist/main.js');
    }
}

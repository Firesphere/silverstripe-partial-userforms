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
        // This should never run on the 'partial' or 'ping' URL
        if (strpos($url, 'partial') !== 0 && !strpos($url, 'ping')) {
            $startNew = true;
            $session = $owner->getRequest()->getSession();
            $id = $session->get(PartialSubmissionController::SESSION_KEY);
            // Check if there is an existing partial submission
            $existing = PartialFormSubmission::get()->byID((int)$id);
            if ($existing) {
                // Check if it has started yet, we need to start a new one, if it has started
                $startNew = $existing->isStarted();
            }

            if ($startNew) {
                $partialForm = PartialFormSubmission::create()->write();
                $session->set(PartialSubmissionController::SESSION_KEY, $partialForm);
            }
        }

        Requirements::javascript('firesphere/partialuserforms:client/dist/main.js');
    }
}

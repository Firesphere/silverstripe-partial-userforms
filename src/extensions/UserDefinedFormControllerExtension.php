<?php

namespace Firesphere\PartialUserforms\Extensions;

use Firesphere\PartialUserforms\Controllers\PartialSubmissionController;
use SilverStripe\Control\NullHTTPRequest;
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
    /**
     * Add required javascripts
     */
    public function onBeforeInit()
    {
        Requirements::javascript('firesphere/partialuserforms:client/dist/main.js');
    }

    /**
     * Start a clean session if the user visits the original form
     */
    public function onAfterInit()
    {
        $request = $this->owner->getRequest();
        if ($request instanceof NullHTTPRequest) {
            return;
        }

        $url = $this->owner->getRequest()->getURL();
        // This should never run on the 'partial' or 'ping' URL
        if (strpos($url, 'partial') === false && strpos($url, 'ping') === false) {
            // Clear session on start
            $session = $this->owner->getRequest()->getSession();
            if ($session && $session->get(PartialSubmissionController::SESSION_KEY)) {
                $session->clear(PartialSubmissionController::SESSION_KEY);
            }
        }
    }
}

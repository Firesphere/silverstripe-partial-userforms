<?php

namespace Firesphere\PartialUserforms\Tasks;

use Firesphere\PartialUserforms\Jobs\PartialSubmissionJob;
use SilverStripe\Control\Email\Email;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\BuildTask;
use SilverStripe\Security\Security;
use SilverStripe\SiteConfig\SiteConfig;

class PartialSubmissionTask extends BuildTask
{
    private static $segment = 'partialsubmissiontask';

    public function __construct()
    {
        $this->title = _t(__CLASS__ . '.Title', 'Export partial form submissions to email address');
        parent::__construct();
    }

    /**
     * Implement this method in the task subclass to
     * execute via the TaskRunner
     *
     * @param HTTPRequest $request
     * @return void
     * @throws \SilverStripe\ORM\ValidationException
     */
    public function run($request)
    {
        $config = SiteConfig::current_site_config();
        $originalEmail = $config->SendEmailTo;
        $currentUser = Security::getCurrentUser();
        if ($currentUser && Email::is_valid_address($currentUser->Email)) {
            $config->SendEmailTo .= ',' . $currentUser->Email;
            $config->write();
        }
        Injector::inst()->get(PartialSubmissionJob::class)->process();

        $config->SendEmailTo = $originalEmail;
        $config->write();
    }
}

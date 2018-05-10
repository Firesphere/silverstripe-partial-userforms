<?php

namespace Firesphere\PartialUserforms\Tasks;

use Firesphere\PartialUserforms\Jobs\PartialSubmissionJob;
use SilverStripe\Control\Email\Email;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\BuildTask;
use SilverStripe\Security\Security;

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
        $currentUser = Security::getCurrentUser();
        $job = Injector::inst()->get(PartialSubmissionJob::class);
        if ($currentUser && Email::is_valid_address($currentUser->Email)) {
            $job->addAddress($currentUser->Email);
        }
        $job->process();
    }
}

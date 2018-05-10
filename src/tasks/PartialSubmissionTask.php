<?php

namespace Firesphere\PartialUserforms\Tasks;

use Firesphere\PartialUserforms\Jobs\PartialSubmissionJob;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\BuildTask;

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
     */
    public function run($request)
    {
        Injector::inst()->get(PartialSubmissionJob::class)->process();
    }
}

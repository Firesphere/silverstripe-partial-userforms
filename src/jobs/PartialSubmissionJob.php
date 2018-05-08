<?php

namespace Firesphere\PartialUserforms\Jobs;

use Symbiote\QueuedJobs\Services\AbstractQueuedJob;

class PartialSubmissionJob extends AbstractQueuedJob
{

    /**
     * @return string
     */
    public function getTitle()
    {
        return _t(__CLASS__ . '.Title', 'Export partial submissions to Email');
    }

    /**
     * Do some processing yourself!
     */
    public function process()
    {
    }
}

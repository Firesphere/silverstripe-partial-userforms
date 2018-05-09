<?php

namespace Firesphere\PartialUserforms\Tests;

use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;

class PartialFormSubmissionTest extends SapphireTest
{
    /**
     * @var PartialFormSubmission
     */
    protected $submission;

    protected function setUp()
    {
        $this->submission = Injector::inst()->get(PartialFormSubmission::class);
        return parent::setUp();
    }

    public function testCanEdit()
    {
        $this->assertFalse($this->submission->canEdit());
    }

    public function testCanDelete()
    {
        $this->assertFalse($this->submission->canDelete());
    }
}
<?php

namespace Firesphere\PartialUserforms\Tests;

use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\UserForms\Model\UserDefinedForm;

class PartialFormSubmissionTest extends SapphireTest
{
    /**
     * @var PartialFormSubmission
     */
    protected $submission;

    public function testGetCMSFields()
    {
        $fields = $this->submission->getCMSFields();

        $this->assertNull($fields->dataFieldByName('Values'));
        $this->assertNull($fields->dataFieldByName('IsSend'));
        $this->assertInstanceOf(GridField::class, $fields->dataFieldByName('PartialFields'));
    }

    public function testCanEdit()
    {
        $this->assertTrue($this->submission->canEdit());
    }

    public function testCanDelete()
    {
        $this->assertTrue($this->submission->canDelete());
    }

    public function testGetParent()
    {
        $formID = UserDefinedForm::create(['Title' => 'Test'])->write();
        $this->submission->UserDefinedFormID = $formID;

        $parent = $this->submission->getParent();

        $this->assertInstanceOf(UserDefinedForm::class, $parent);
    }

    protected function setUp()
    {
        $this->submission = Injector::inst()->get(PartialFormSubmission::class);
        $this->usesDatabase = true;

        return parent::setUp();
    }
}

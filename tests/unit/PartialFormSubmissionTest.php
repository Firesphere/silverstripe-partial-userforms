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
     * @var bool
     */
    protected $usesDatabase = true;

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

    public function testCanCreate()
    {
        $this->assertTrue($this->submission->canCreate());

        $this->submission->ParentID = $this->submission->UserDefinedFormID;
        $this->submission->UserDefinedFormID = 0;

        $this->assertTrue($this->submission->canCreate());
    }

    public function testCanView()
    {
        $this->assertTrue($this->submission->canView());

        $this->submission->ParentID = $this->submission->UserDefinedFormID;
        $this->submission->UserDefinedFormID = 0;

        $this->assertTrue($this->submission->canView());
    }

    public function testCanEdit()
    {
        $this->assertTrue($this->submission->canEdit());

        $this->submission->ParentID = $this->submission->UserDefinedFormID;
        $this->submission->UserDefinedFormID = 0;

        $this->assertTrue($this->submission->canEdit());
    }

    public function testCanDelete()
    {
        $this->assertTrue($this->submission->canDelete());

        $this->submission->ParentID = $this->submission->UserDefinedFormID;
        $this->submission->UserDefinedFormID = 0;

        $this->assertTrue($this->submission->canDelete());
    }

    public function testGetParent()
    {
        $parent = $this->submission->getParent();

        $this->assertInstanceOf(UserDefinedForm::class, $parent);
    }

    protected function setUp()
    {
        $this->submission = Injector::inst()->get(PartialFormSubmission::class);
        $form = UserDefinedForm::create(['Title' => 'Test']);
        $formID = $form->write();
        $this->submission->UserDefinedFormID = $formID;
        $this->submission->UserDefinedFormClass = UserDefinedForm::class;
        $this->submission->write();

        return parent::setUp();
    }
}

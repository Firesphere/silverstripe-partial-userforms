<?php

namespace Firesphere\PartialUserforms\Tests;

use Firesphere\PartialUserforms\Models\PartialFieldSubmission;
use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Security\DefaultAdminService;
use SilverStripe\Security\Security;
use SilverStripe\UserForms\Model\UserDefinedForm;

class PartialFieldSubmissionTest extends SapphireTest
{

    /**
     * @var PartialFieldSubmission
     */
    protected $field;

    public function testCanView()
    {
        $this->assertTrue($this->field->canView());

        $member = DefaultAdminService::singleton()->findOrCreateAdmin('admin@example.com');
        Security::setCurrentUser($member);

        $this->assertTrue($this->field->canView($member));
    }

    public function testCanCreate()
    {
        Security::setCurrentUser(null);
        $this->assertFalse($this->field->canCreate());

        $member = DefaultAdminService::singleton()->findOrCreateAdmin('admin@example.com');
        Security::setCurrentUser($member);

        $this->assertTrue($this->field->canCreate($member));
    }

    public function testCanEdit()
    {
        Security::setCurrentUser(null);
        $this->assertFalse($this->field->canEdit());

        $member = DefaultAdminService::singleton()->findOrCreateAdmin('admin@example.com');
        Security::setCurrentUser($member);

        $this->assertTrue($this->field->canEdit($member));
    }

    public function testCanDelete()
    {
        Security::setCurrentUser(null);
        $this->assertFalse($this->field->canDelete());

        $member = DefaultAdminService::singleton()->findOrCreateAdmin('admin@example.com');
        Security::setCurrentUser($member);

        $this->assertTrue($this->field->canDelete($member));
    }

    protected function setUp()
    {
        $this->field = PartialFieldSubmission::create();
        $partialForm = PartialFormSubmission::create();
        $udf = UserDefinedForm::create(['Title' => 'Test'])->write();
        $partialForm->UserDefinedFormID = $udf;
        $partialFormID = $partialForm->write();
        $this->field->SubmittedFormID = $partialFormID;

        return parent::setUp();
    }
}

<?php

namespace Firesphere\PartialUserforms\Tests;

use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
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
        $this->assertNull($fields->dataFieldByName('TokenSalt'));
        $this->assertNull($fields->dataFieldByName('Token'));
        $this->assertNull($fields->dataFieldByName('UserDefinedFormID'));
        $this->assertInstanceOf(GridField::class, $fields->dataFieldByName('PartialFields'));
    }

    public function testCanCreate()
    {
        $this->assertFalse($this->submission->canCreate());

        $this->submission->ParentID = $this->submission->UserDefinedFormID;
        $this->submission->UserDefinedFormID = 0;

        $this->assertFalse($this->submission->canCreate());
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

    public function testGetPartialLink()
    {
        $partial = PartialFormSubmission::create();
        $this->assertEquals('(none)', $partial->getPartialLink());

        $partial->write();
        $this->assertNotEquals('(none)', $partial->getPartialLink());

        $partial->Token = 'test-token';
        $partial->TokenSalt = 'test-salt';
        $partial->write();

        $link = Controller::join_links(
            Director::absoluteBaseURL(),
            'partial',
            $partial->generateKey('test-token'),
            'test-token'
        );
        $this->assertEquals($link, $partial->getPartialLink());
    }

    public function testGetPartialToken()
    {
        $partial = PartialFormSubmission::create();
        $partial->TokenSalt = 'test-salt';
        $this->assertNull(TestHelper::invokeMethod($partial, 'getPartialToken'));

        $partial->TokenSalt = null;
        $partial->write();
        $this->assertNotNull(TestHelper::invokeMethod($partial, 'getPartialToken'));

        $partial->Token = 'test-token';
        $partial->TokenSalt = 'test-salt';
        $partial->write();

        $this->assertEquals('test-token', TestHelper::invokeMethod($partial, 'getPartialToken'));
    }

    public function testGenerateToken()
    {
        $partial = singleton(PartialFormSubmission::class);
        $token = TestHelper::invokeMethod($partial, 'generateToken');
        $this->assertNotNull($token);
        $this->assertEquals(16, strlen($token));
    }

    public function testGenerateKey()
    {
        $partial = PartialFormSubmission::create();
        $token = 'test-token';
        $this->assertEquals('b041b97441e59b3a', $partial->generateKey($token));

        $partial->TokenSalt = 'test-salt';
        $key = $partial->generateKey($token);
        $this->assertEquals('30505a53806d5de9', $key);
        $this->assertEquals(16, strlen($key));
    }

    public function testGetFieldList()
    {
        $partial = PartialFormSubmission::create();
        $this->assertEmpty($partial->getFieldList());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->submission = Injector::inst()->get(PartialFormSubmission::class);
        $form = UserDefinedForm::create(['Title' => 'Test']);
        $formID = $form->write();
        $this->submission->UserDefinedFormID = $formID;
        $this->submission->UserDefinedFormClass = UserDefinedForm::class;
        $this->submission->write();
    }
}

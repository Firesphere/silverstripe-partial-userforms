<?php

namespace Firesphere\PartialUserforms\Tests;

use Firesphere\PartialUserforms\Controllers\PartialUserFormController;
use Firesphere\PartialUserforms\Extensions\SubmittedFormExtension;
use Firesphere\PartialUserforms\Models\PartialFieldSubmission;
use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\UserForms\Model\Submission\SubmittedForm;

class SubmittedFormExtensionTest extends SapphireTest
{
    protected static $fixture_file = '../fixtures/partialformtest.yml';

    public function testClassExists()
    {
        $extension = Injector::inst()->get(SubmittedFormExtension::class);

        $this->assertInstanceOf(SubmittedFormExtension::class, $extension);
    }

    public function testUpdateAfterProcessForm()
    {
        $partialSubmission = $this->objFromFixture(PartialFormSubmission::class, 'submission1');
        Controller::curr()->getRequest()->getSession()->set(
            PartialUserFormController::SESSION_KEY,
            $partialSubmission->ID
        );

        $extension = Injector::inst()->get(PartialFormSubmission::class);

        $extension->updateAfterProcess();

        $submission = PartialFormSubmission::get()->byID($partialSubmission->ID);

        $this->assertNull($submission);
    }

    public function testUpdateAfterProcessFields()
    {
        $partialSubmission = $this->objFromFixture(PartialFormSubmission::class, 'submission1');
        Controller::curr()->getRequest()->getSession()->set(
            PartialUserFormController::SESSION_KEY,
            $partialSubmission->ID
        );

        $extension = Injector::inst()->get(PartialFormSubmission::class);

        $submissionFields = PartialFieldSubmission::get()->filter(['SubmittedFormID' => $partialSubmission->ID]);

        $this->assertEquals(3, $submissionFields->count());

        $extension->updateAfterProcess();

        $submissionFields = PartialFieldSubmission::get()->filter(['ParentID' => $partialSubmission->ID]);

        $this->assertEquals(0, $submissionFields->count());
    }

    public function testUpdateAfterProcessSession()
    {
        $partialSubmission = $this->objFromFixture(PartialFormSubmission::class, 'submission1');
        Controller::curr()->getRequest()->getSession()->set(
            PartialUserFormController::SESSION_KEY,
            $partialSubmission->ID
        );

        $extension = Injector::inst()->get(PartialFormSubmission::class);

        $extension->updateAfterProcess();

        $session = Controller::curr()->getRequest()->getSession()->get(PartialUserFormController::SESSION_KEY);
        $this->assertNull($session);
    }

    public function testIsPartial()
    {
        /** @var SubmittedFormExtension $extension */
        $extension = Injector::inst()->get(SubmittedFormExtension::class);
        $submission = PartialFormSubmission::create();
        $extension->setOwner($submission);

        $this->assertEquals('Partial submission', $extension->getIsPartial());
    }

    public function testIsNotPartial()
    {
        /** @var SubmittedFormExtension $extension */
        $extension = Injector::inst()->get(SubmittedFormExtension::class);
        $submission = SubmittedForm::create();
        $extension->setOwner($submission);

        $this->assertEquals('Completed submission', $extension->getIsPartial());
    }

    protected function setUp()
    {
        parent::setUp();
    }
}

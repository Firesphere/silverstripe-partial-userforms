<?php

namespace Firesphere\PartialUserforms\Tests;

use Firesphere\PartialUserforms\Controllers\PartialSubmissionController;
use Firesphere\PartialUserforms\Controllers\PartialUserFormVerifyController;
use Firesphere\PartialUserforms\Forms\PasswordForm;
use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use SilverStripe\Control\NullHTTPRequest;
use SilverStripe\Control\Session;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\Dev\SapphireTest;

class PartialUserFormVerifyControllerTest extends FunctionalTest
{
    protected static $fixture_file = '../fixtures/partialformtest.yml';

    /**
     * @expectedException  \SilverStripe\Control\HTTPResponse_Exception
     */
    public function testInit()
    {
        $request = new NullHTTPRequest();
        $session = new Session(['hi' => 'bye']);
        $request->setSession($session);
        $controller = PartialUserFormVerifyController::create();
        $controller->setRequest($request);

        $controller->init();
    }

    public function testInitSuccess()
    {
        $partialForm = $this->objFromFixture(PartialFormSubmission::class, 'submission1');
        $request = new NullHTTPRequest();
        $session = new Session(['hi' => 'bye', PartialSubmissionController::SESSION_KEY => $partialForm->ID]);
        $request->setSession($session);
        $controller = PartialUserFormVerifyController::create();
        $controller->setRequest($request);

        $controller->init();
        $this->assertEquals(200, $controller->getResponse()->getStatusCode());
    }

    public function testDoValidate()
    {
        $request = new NullHTTPRequest();
        /** @var PartialFormSubmission $partialForm */
        $partialForm = $this->objFromFixture(PartialFormSubmission::class, 'submission1');
        $parent = $partialForm->getParent();
        $parent->PasswordProtected = true;
        $parent->write();
        $parent->publishRecursive();
        $partialForm->ParentID = $parent->ID;
        $partialForm->write();
        $session = new Session(['hi' => 'bye', PartialSubmissionController::SESSION_KEY => $partialForm->ID]);
        $request->setSession($session);
        $controller = PartialUserFormVerifyController::create();
        $controller->setRequest($request);

        $this->assertNotNull($partialForm->Password);

        $testPwd = hash_pbkdf2('SHA256', '1234567890', $partialForm->TokenSalt, 1000);

        $partialForm->Password = $testPwd;
        $partialForm->write();

        $controller->init();
        $form = new PasswordForm($controller, __FUNCTION__);
        $result = $controller->doValidate(['Password' => '1234567890'], $form);

        $this->assertEquals($partialForm->ID, $session->get('PartialFormSession'));
        $this->assertContains('/partial/', $result->getHeader('Location'));
        $this->assertEquals('1234567890', $session->get(PartialUserFormVerifyController::PASSWORD_KEY));

        $controller = new PartialUserFormVerifyController();
        $controller->setRequest($request);
        $controller->init();
        $result = $controller->doValidate(['Password' => '0987654321'], $form);

        // It uses redirect back, so it's unclear what the 'back' is at this stage
        $this->assertNotContains('/partial', $result->getHeader('Location'));
    }
}

<?php

namespace Firesphere\PartialUserforms\Tests;

use Firesphere\PartialUserforms\Controllers\PartialUserFormController;
use Firesphere\PartialUserforms\Models\PartialFieldSubmission;
use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\Session;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;

class PartialUserFormControllerTest extends SapphireTest
{
    protected static $fixture_file = '../fixtures/partialformtest.yml';

    /**
     * @var PartialUserFormController
     */
    protected $controller;

    public function setUp()
    {
        $this->controller = Injector::inst()->get(PartialUserFormController::class);
        parent::setUp();
    }

    public function testClassExists()
    {
        $this->assertInstanceOf(PartialUserFormController::class, $this->controller);
    }

    public function testSavePartialSubmissionExists()
    {
        $this->assertTrue(method_exists($this->controller, 'savePartialSubmission'));
    }

    public function testSavePartialSubmissionFormCreated()
    {
        $request = new HTTPRequest('POST', '/partialuserform', [], []);
        $session = new Session(['hi' => 'bye']);
        $request->setSession($session);

        $id = $this->controller->savePartialSubmission($request);

        $this->assertInternalType('numeric', $id);

        $form = PartialFormSubmission::get()->byID($id);

        $this->assertInstanceOf(PartialFormSubmission::class, $form);
    }

    public function testSavePartialSubmissionFieldCreated()
    {
        $request = new HTTPRequest('POST', '/partialuserform', [], ['Field1' => 'Value1']);
        $session = new Session(['hi' => 'bye']);
        $request->setSession($session);

        $id = $this->controller->savePartialSubmission($request);

        $fields = PartialFieldSubmission::get()->filter(['SubmittedFormID' => $id]);

        $this->assertEquals(1, $fields->count());
    }

    public function testPartialFormSubmissionExists()
    {
        $request = new HTTPRequest('POST', '/partialuserform', [], ['Field1' => 'Value1', 'Field2' => 'Value2']);
        $session = new Session(['hi' => 'bye']);
        $request->setSession($session);

        $id = $this->controller->savePartialSubmission($request);

        $session = $request->getSession();
        $request = new HTTPRequest('POST', '/partialuserform', [], ['Field2' => 'Value2']);
        $request->setSession($session);

        $secondId = $this->controller->savePartialSubmission($request);

        $this->assertEquals($id, $secondId);
    }

    public function testPartialFormSubmissionExistingField()
    {
        $values = [
            'Field1' => 'Value1',
            'Field2' => 'Value2',
            'Field3' => 'null'
        ];
        $request = new HTTPRequest('POST', '/partialuserform', [], $values);
        $session = new Session(['hi' => 'bye']);
        $request->setSession($session);

        $this->controller->savePartialSubmission($request);
        $sessionKey = $session->get(PartialUserFormController::SESSION_KEY);
        $field3 = PartialFieldSubmission::get()
            ->filter([
                'Name'            => 'Field3',
                'SubmittedFormID' => $sessionKey
            ])
            ->first();

        $this->assertEquals('null', $field3->Value);
        // Update the values
        $values['Field3'] = 'Value3';
        $request = new HTTPRequest('POST', '/partialuserform', [], $values);
        $request->setSession($session);
        $this->controller->savePartialSubmission($request);
        $sessionKey = $session->get(PartialUserFormController::SESSION_KEY);

        $field3 = PartialFieldSubmission::get()
            ->filter([
                'Name'            => 'Field3',
                'SubmittedFormID' => $sessionKey
            ])
            ->first();
        $this->assertEquals('Value3', $field3->Value);
    }
}

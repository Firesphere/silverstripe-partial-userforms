<?php

namespace Firesphere\PartialUserforms\Tests;

use Firesphere\PartialUserforms\Controllers\PartialSubmissionController;
use Firesphere\PartialUserforms\Models\PartialFieldSubmission;
use Firesphere\PartialUserforms\Models\PartialFileFieldSubmission;
use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use SilverStripe\Assets\Upload_Validator;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\ORM\DataList;
use SilverStripe\UserForms\Model\EditableFormField\EditableFileField;
use SilverStripe\UserForms\Model\UserDefinedForm;

/**
 * Class PartialSubmissionControllerTest
 * @package Firesphere\PartialUserforms\Tests
 */
class PartialSubmissionControllerTest extends FunctionalTest
{
    protected static $fixture_file = '../fixtures/partialformtest.yml';

    /**
     * @var PartialSubmissionController
     */
    protected $controller;

    public function testClassExists()
    {
        $this->assertInstanceOf(PartialSubmissionController::class, $this->controller);
    }

    public function testSavePartialSubmissionExists()
    {
        $this->assertTrue(method_exists($this->controller, 'savePartialSubmission'));
    }

    public function testSavePartialSubmissionFormCreated()
    {
        // If successful, will return the SubmittedFormID from the session
        $id = $this->savePartial(['Field1' => 'Value1']);
        $this->assertInternalType('int', $id);

        $partial = PartialFormSubmission::get()->byID($id);
        $this->assertNotNull($partial);
    }

    public function testSavePartialSubmissionFieldCreated()
    {
        // If successful, will return the SubmittedFormID from the session
        $id = $this->savePartial(['Field1' => 'Value1']);
        $this->assertInternalType('int', $id);

        $fields = PartialFieldSubmission::get()->filter(['SubmittedFormID' => $id]);
        $this->assertCount(1, $fields);
    }

    public function testSavePartialSubmissionFileField()
    {
        $field = $this->objFromFixture(EditableFileField::class, 'filefield1');
        $field->write();
        copy(__DIR__ . '/../fixtures/Hans-fullsize-sqr.png', TEMP_FOLDER . '/tmpfile');
        $multipart = [
            'name'     => 'file1',
            'tmp_name' => TEMP_FOLDER . '/tmpfile',
            'filename' => 'Hans-fullsize-sqr.png',
            'type'     => 'image/png',
            'error'    => null,
            'size'     => filesize(TEMP_FOLDER . '/tmpfile')
        ];
        // Disable checking if the file is uploaded through a POST
        Upload_Validator::config()->set('use_is_uploaded_file', false);
        $this->savePartial(['File' => $multipart]);
        $this->assertNotNull(PartialFileFieldSubmission::get()->filter(['Name' => 'File']));
    }

    public function testPartialFormSubmissionExists()
    {
        // If successful, will return the SubmittedFormID from the session
        $id = $this->savePartial(['Field1' => 'Value1', 'Field2' => 'Value2']);

        // Second submission
        $secondId = $this->savePartial(['Field2' => 'Value2']);

        $this->assertEquals($id, $secondId);
    }

    public function testPartialFormSubmissionExistingField()
    {
        $values = [
            'Field1' => 'Value1',
            'Field2' => 'Value2',
            'Field3' => 'null'
        ];
        $id = $this->savePartial($values);
        $field3 = PartialFieldSubmission::get()
            ->filter([
                'Name'            => 'Field3',
                'SubmittedFormID' => $id
            ])
            ->first();

        $this->assertEquals('null', $field3->Value);

        // Update the values
        $values['Field3'] = 'Value3';
        $id = $this->savePartial($values);
        $field3 = PartialFieldSubmission::get()
            ->filter([
                'Name'            => 'Field3',
                'SubmittedFormID' => $id
            ])
            ->first();
        $this->assertEquals('Value3', $field3->Value);
    }

    public function testSubmittedFieldName()
    {
        $values = [
            'Field1' => 'Value1',
            'Field2' => 'Value2',
            'Field3' => 'null'
        ];
        $id = $this->savePartial($values);
        /** @var DataList|PartialFieldSubmission[] $fields */
        $fields = PartialFieldSubmission::get()->filter(['SubmittedFormID' => $id]);
        $this->assertCount(3, $fields);

        foreach ($fields as $key => $field) {
            $this->assertEquals('Field' . ($key + 1), $field->Name, 'Test field ' . $field->Name);
        }
    }

    public function testParent()
    {
        $values = [
            'Field1' => 'Value1',
            'Field2' => 'Value2',
            'Field3' => 'null'
        ];
        $id = $this->savePartial($values);
        /** @var DataList|PartialFieldSubmission[] $fields */
        $partialForm = PartialFormSubmission::get()->byID($id);

        // No parent class
        $this->assertEquals(UserDefinedForm::class, $partialForm->ParentClass);

        $form = UserDefinedForm::create(['Title' => 'Test']);
        $formID = $form->write();

        // With parent class
        $partialForm->ParentID = $formID;
        $partialForm->ParentClass = get_class($form);

        $this->assertEquals(UserDefinedForm::class, $partialForm->ParentClass);
    }

    public function testUnwantedFields()
    {
        $values = [
            'Field1'         => 'Value1',
            'Field2'         => 'Value2',
            'Field3'         => 'null',
            'SecurityID'     => '123456789aoeu',
            'action_process' => 'Submit'
        ];
        $id = $this->savePartial($values);
        /** @var DataList|PartialFieldSubmission[] $fields */
        $fields = PartialFieldSubmission::get()->filter(['SubmittedFormID' => $id]);

        $items = $fields->column('Name');
        $this->assertFalse(in_array('SecurityID', $items, true));
        $this->assertFalse(in_array('action_process', $items, true));
    }

    public function testArrayData()
    {
        $values = [
            'Field1' => 'Value1',
            'Field2' => 'Value2',
            'Field3' => ['Value1', 'Value2']
        ];
        $id = $this->savePartial($values);
        $field3 = PartialFieldSubmission::get()
            ->filter([
                'Name'            => 'Field3',
                'SubmittedFormID' => $id
            ])
            ->first();
        $this->assertEquals('Value1, Value2', $field3->Value);
    }

    /**
     * @todo Remove skip test after implementation
     */
    public function testSaveDataWithExpiredSession()
    {
        $this->markTestSkipped('Remove skip test once implementation is complete');

        $values = [
            'Field1' => 'Value1',
            'Field2' => 'Value2',
            'Field3' => ['Value1', 'Value2']
        ];

        $id = $this->savePartial($values);
        $this->assertInternalType('int', $id);

        $partial = PartialFormSubmission::get()->byID($id);
        $this->assertNotNull($partial);

        // Now clear session and save
        $this->session()->clear(PartialSubmissionController::SESSION_KEY);
        $values['Field1'] = 'NEW VALUE';
        $newId = $this->savePartial($values);
        $this->assertEquals($id, $newId);
    }

    /**
     * Helper function for saving partial submission
     * @param array $values
     * @return mixed Returns SubmittedFormID on success, otherwise null
     */
    private function savePartial($values)
    {
        $response = $this->post('/partialuserform/save', $values);
        $this->assertEquals(201, $response->getStatusCode());

        return $this->session()->get(PartialSubmissionController::SESSION_KEY);
    }

    public function setUp()
    {
        parent::setUp();
        $this->objFromFixture(UserDefinedForm::class, 'form1')->publishRecursive();
        $this->controller = Injector::inst()->get(PartialSubmissionController::class);
    }
}

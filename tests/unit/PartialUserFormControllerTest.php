<?php

namespace Firesphere\PartialUserforms\Tests;

use Firesphere\PartialUserforms\Forms\PasswordForm;
use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use SilverStripe\Assets\File;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\UserForms\Model\UserDefinedForm;

/**
 * Class PartialUserFormControllerTest
 * @package Firesphere\PartialUserforms\Tests
 */
class PartialUserFormControllerTest extends FunctionalTest
{
    /**
     * @var string
     */
    protected static $fixture_file = '../fixtures/partialformtest.yml';

    public function testPartialPage()
    {
        $result = $this->get("partial");
        $this->assertEquals(404, $result->getStatusCode());
    }

    public function testPartialValidKeyToken()
    {
        $token = 'q1w2e3r4t5y6u7i8';
        // No Parent
        $key = singleton(PartialFormSubmission::class)->generateKey($token);
        $result = $this->get("partial/{$key}/{$token}");
        $this->assertEquals(404, $result->getStatusCode());

        // Partial with UserDefinedForm
        $key = $this->objFromFixture(PartialFormSubmission::class, 'submission1')->generateKey($token);
        $result = $this->get("partial/{$key}/{$token}");
        $this->assertEquals(200, $result->getStatusCode());
        $this->assertContains('Field 1', $result->getBody());
    }

    public function testPartialInvalidToken()
    {
        $token = 'abcdef';
        $key = singleton(PartialFormSubmission::class)->generateKey($token);

        $result = $this->get("partial/{$key}/{$token}");
        $this->assertEquals(404, $result->getStatusCode());
    }

    public function testPartialInvalidKey()
    {
        $token = 'e6b27462211e1711';
        $key = 'abcdef';

        $result = $this->get("partial/{$key}/{$token}");
        $this->assertEquals(404, $result->getStatusCode());

        $token = 'qwerty';
        $key = 'abcdef';

        $result = $this->get("partial/{$key}/{$token}");
        $this->assertEquals(404, $result->getStatusCode());
    }

    public function testDataPopulated()
    {
        $partial = $this->objFromFixture(PartialFormSubmission::class, 'submission1');
        $key = $partial->generateKey($partial->Token);

        $response = $this->get("/partial/{$key}/{$partial->Token}");
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertCount(1, $partial->PartialUploads());
        $this->assertCount(3, $partial->PartialFields());

        $content = $response->getBody();
        $this->assertContains('I have a question', $content);
        $this->assertContains('Hans-fullsize-sqr.png', $content);
    }

    public function testPasswordProtectedPartial()
    {
        // Partial with UserDefinedForm
        $submission = $this->objFromFixture(PartialFormSubmission::class, 'submission1');
        /** @var UserDefinedForm $parent */
        $parent = $submission->Parent();
        $parent->PasswordProtected = true;
        $parent->write();
        $parent->publishRecursive();

        $key = $submission->generateKey($submission->Token);
        $result = $this->get("partial/{$key}/{$submission->Token}");
        // Be redirected to the Password form
        $formOpeningTag = '<form id="PasswordForm_getForm" action="/verify/getForm" method="post" enctype="application/x-www-form-urlencoded" class="userform">';
        $this->assertContains($formOpeningTag, $result->getBody());
    }

    public function setUp()
    {
        parent::setUp();
        $this->objFromFixture(UserDefinedForm::class, 'form1')->publishRecursive();
        $this->objFromFixture(File::class, 'file1')->publishRecursive();
    }
}

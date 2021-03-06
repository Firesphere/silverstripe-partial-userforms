<?php

namespace Firesphere\PartialUserforms\Tests;

use Firesphere\PartialUserforms\Models\PartialFormSubmission;
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

    /**
     * @todo
     */
    public function testPartialValidKeyToken()
    {
        $this->markTestSkipped('Revisit and set up themes for testing');

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

    public function testPasswordProtectedPartial()
    {
        $token = 'q1w2e3r4t5y6u7i8';
        // Partial with UserDefinedForm
        $submission = $this->objFromFixture(PartialFormSubmission::class, 'submission1');
        /** @var UserDefinedForm $parent */
        $parent = $submission->Parent();
        $parent->PasswordProtected = true;
        $parent->write();
        $parent->publishRecursive();
        $key = $submission->generateKey($token);
        $result = $this->get("partial/{$key}/{$token}");
        // Be redirected to the Password form
        $formOpeningTag = '<form id="PasswordForm_getForm" action="/verify/getForm" method="post" enctype="application/x-www-form-urlencoded" class="userform">';
        $this->assertContains($formOpeningTag, $result->getBody());
    }

    public function setUp()
    {
        parent::setUp();
        $this->objFromFixture(UserDefinedForm::class, 'form1')->publishRecursive();
    }
}

<?php

namespace Firesphere\PartialUserforms\Tests;

use Firesphere\PartialUserforms\Models\PartialFormSubmission;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\UserForms\Model\UserDefinedForm;

/**
 * Class PartialUserFormControllerFunctionalTest
 * @package Firesphere\PartialUserforms\Tests
 */
class PartialUserFormControllerFunctionalTest extends FunctionalTest
{
    /**
     * @var string
     */
    protected static $fixture_file = '../fixtures/partialformtest.yml';

    public function setUp()
    {
        parent::setUp();

        $this->objFromFixture(UserDefinedForm::class, 'form1')->publishRecursive();
    }

    public function testPartialPage()
    {
        $result = $this->get("partial");
        $this->assertEquals(404, $result->getStatusCode());
    }

    public function xtestPartialValidKeyToken()
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
}

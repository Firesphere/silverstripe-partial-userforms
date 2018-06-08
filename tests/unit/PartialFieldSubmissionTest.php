<?php

namespace Firesphere\PartialUserforms\Tests;

use Firesphere\PartialUserforms\Models\PartialFieldSubmission;
use SilverStripe\Dev\SapphireTest;

class PartialFieldSubmissionTest extends SapphireTest
{

    /**
     * @var PartialFieldSubmission
     */
    protected $field;
    
    public function testCanView()
    {
        $this->assertFalse($this->field->canView());
    }
    
    public function testCanCreate()
    {
        $this->assertTrue($this->field->canCreate());
    }
    
    public function testCanEdit()
    {
        $this->assertFalse($this->field->canEdit());
    }
    
    public function testCanDelete()
    {
        $this->assertFalse($this->field->canDelete());
    }
    
    protected function setUp()
    {
        $this->field = PartialFieldSubmission::create();
        return parent::setUp();
    }
}
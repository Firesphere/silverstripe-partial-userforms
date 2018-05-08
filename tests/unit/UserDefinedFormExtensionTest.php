<?php

namespace Firesphere\PartialUserforms\Tests;

use Firesphere\PartialUserforms\Extensions\UserDefinedFormExtension;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Tab;
use SilverStripe\UserForms\Model\UserDefinedForm;

class UserDefinedFormExtensionTest extends SapphireTest
{
    public function testCMSFields()
    {
        $extension = Injector::inst()->get(UserDefinedFormExtension::class);

        $fields = FieldList::create();
        $fields->add(Tab::create('Root'));
        $fields->add(Tab::create('Root.PartialSubmissions'));

        $extension->setOwner(UserDefinedForm::create());

        $extension->updateCMSFields($fields);

        $this->assertNotNull($fields->dataFieldByName('PartialSubmissions'));
    }
}

<?php

namespace Firesphere\PartialUserforms\Tests;

use Firesphere\PartialUserforms\Extensions\UserDefinedFormExtension;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\UserForms\Model\UserDefinedForm;

class UserDefinedFormExtensionTest extends SapphireTest
{
    /** @var bool */
    protected $usesDatabase = true;

    public function testCMSFields()
    {
        $extension = Injector::inst()->get(UserDefinedFormExtension::class);
        $form = UserDefinedForm::create(['Title' => 'Test']);
        $form->write();
        $extension->setOwner($form);
        $fields = $form->getCMSFields();

        $extension->updateCMSFields($fields);

        $this->assertNotNull($fields->dataFieldByName('PartialSubmissions'));
        $this->assertNotNull($fields->dataFieldByName('ExportPartialSubmissions'));
        $this->assertNotNull($fields->dataFieldByName('PasswordProtected'));
    }
}

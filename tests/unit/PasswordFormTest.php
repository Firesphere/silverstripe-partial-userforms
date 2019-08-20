<?php


namespace Firesphere\PartialUserforms\Tests;

use Firesphere\PartialUserforms\Controllers\PartialUserFormVerifyController;
use Firesphere\PartialUserforms\Forms\PasswordForm;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\PasswordField;

class PasswordFormTest extends SapphireTest
{
    public function testConstruct()
    {
        $page = new \Page();
        $controller = new PartialUserFormVerifyController($page);
        $form = new PasswordForm($controller, 'PasswordForm');

        $this->assertInstanceOf(Form::class, $form);
        $this->assertInstanceOf(PasswordField::class, $form->Fields()->dataFieldByName('Password'));
        $this->assertInstanceOf(FieldList::class, $form->Actions());
    }
}

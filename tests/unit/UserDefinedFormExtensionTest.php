<?php

namespace Firesphere\PartialUserforms\Tests;

use Firesphere\PartialUserforms\Extensions\UserDefinedFormExtension;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;

class UserDefinedFormExtensionTest extends SapphireTest
{
    public function testCMSFields()
    {
        $extension = Injector::inst()->get(UserDefinedFormExtension::class);
    }
}

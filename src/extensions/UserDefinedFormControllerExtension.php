<?php

namespace Firesphere\PartialUserforms\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\UserForms\Control\UserDefinedFormController;
use SilverStripe\View\Requirements;

/**
 * Class UserDefinedFormControllerExtension
 * @package Firesphere\PartialUserforms\Extensions
 * @property UserDefinedFormController|UserDefinedFormControllerExtension $owner
 */
class UserDefinedFormControllerExtension extends Extension
{
    public function onBeforeInit()
    {
        Requirements::javascript('firesphere/partialuserforms:client/dist/main.js');
    }
}

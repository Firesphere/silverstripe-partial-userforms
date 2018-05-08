<?php

namespace Firesphere\PartialUserforms\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\View\Requirements;

class UserDefinedFormControllerExtension extends Extension
{
    public function onBeforeInit()
    {
        Requirements::javascript('firesphere/partialuserforms:client/dist/main.js');
    }
}

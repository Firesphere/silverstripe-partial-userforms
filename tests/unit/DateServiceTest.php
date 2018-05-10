<?php

namespace Firesphere\PartialUserforms\Tests;

use Firesphere\PartialUserforms\Services\DateService;
use SilverStripe\Dev\SapphireTest;

class DateServiceTest extends SapphireTest
{
    public function testGetTomorrow()
    {
        $tomorrow = DateService::getTomorrow();

        $this->assertEquals(date('d', strtotime('+1 day')), $tomorrow->Format('dd'));
    }
}

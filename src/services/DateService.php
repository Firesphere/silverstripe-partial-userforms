<?php

namespace Firesphere\PartialUserforms\Services;


use DateInterval;
use DateTime;
use SilverStripe\ORM\FieldType\DBDatetime;

class DateService
{

    /**
     * @return DBDatetime|static
     * @throws \Exception
     */
    public static function getTomorrow()
    {
        $dateTime = new DateTime(DBDatetime::now());
        $interval = new DateInterval('P1D');
        $tomorrow = $dateTime->add($interval);
        $dbDateTime = DBDatetime::create();
        $dbDateTime->setValue($tomorrow->format('Y-m-d 00:00:00'));

        return $dbDateTime;
    }
}
<?php

namespace Firesphere\PartialUserforms\Tests;

use SilverStripe\SiteConfig\SiteConfig;

/**
 * Class SetupSiteConfig is a simple helper for the setting up of tests.
 * To avoid having to re-type the siteconfig creation/setup every time
 * @package Firesphere\PartialUserforms\Tests
 */
class SiteConfigHelper
{
    public static function setupSiteConfig($addresses = null, $from = null, $send = true, $cleanup = true)
    {
        $config = SiteConfig::current_site_config();
        $config->SendMailTo = $addresses;
        $config->SendDailyEmail = $send;
        $config->CleanupAfterSend = $cleanup;
        $config->SendMailFrom = $from;
        $config->write();
    }
}

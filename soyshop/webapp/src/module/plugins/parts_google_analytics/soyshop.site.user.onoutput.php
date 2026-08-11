<?php
include_once(SOY2::RootDir() . "logic/plugin/extensions/soyshop.site.onoutput.php");
include_once(dirname(__FILE__) . "/soyshop.site.onoutput.php");
SOYShopPlugin::extension("soyshop.site.user.onoutput", "parts_google_analytics", "GoogleAnalyticsOnOutput");

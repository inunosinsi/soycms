<?php
include_once(SOY2::RootDir() . "logic/plugin/extensions/soyshop.site.onoutput.php");
include_once(dirname(__FILE__) . "/soyshop.site.onoutput.php");
SOYShopPlugin::extension("soyshop.site.user.onoutput", "async_cart_button", "AsyncCartButtonOnOutput");

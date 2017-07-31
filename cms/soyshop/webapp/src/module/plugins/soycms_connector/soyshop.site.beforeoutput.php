<?php
/*
 * soyshop.site.beforeoutput.php
 * Created: 2010/03/11
 */

class SOYCMSConnectorBeforeOutput extends SOYShopSiteBeforeOutputAction{

    function beforeOutput($page){
      if(!defined("SOYCMS_COMMON_DIR")) {
        define("SOYCMS_COMMON_DIR", dirname(dirname(SOYSHOP_WEBAPP)) . "/common/");
      }

      SOY2::import("module.plugins.soycms_connector.util.SOYCMSConnectorUtil");
      $config = SOYCMSConnectorUtil::getConfig();
      if(!defined("SOYCMS_SITE_ID")) define("SOYCMS_SITE_ID", $config["siteId"]);

      SOY2::import("module.plugins.soycms_connector.component.SOYShop_SOYCMSPageModulePlugin");
      SOYShop_SOYCMSPageModulePlugin::configure(array(
        "siteId" => SOYCMS_SITE_ID,
        "rootDir" => SOYCMS_COMMON_DIR
      ));
      SOYShop_SOYCMSPageModulePlugin::prepare(true);

      $plugin = new SOYShop_SOYCMSPageModulePlugin();
      $page->executePlugin("module","[a-zA-Z0-9\.\_]+",$plugin);

      //戻す
      SOYShop_SOYCMSPageModulePlugin::tearDown();
    }
}

SOYShopPlugin::extension("soyshop.site.beforeoutput", "util_multi_languag", "SOYCMSConnectorBeforeOutput");

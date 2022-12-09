<?php
/*
 * soyshop.site.beforeoutput.php
 * Created: 2010/03/11
 */

class UtilMultiLanguageBeforeOutput extends SOYShopSiteBeforeOutputAction{

    function beforeOutput($page){
        SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
        foreach(UtilMultiLanguageUtil::allowLanguages() as $lang => $title){
            $page->addLink("language_" . $lang . "_link", array(
                "soy2prefix" => SOYSHOP_SITE_PREFIX,
                "link" => "?language=" . $lang
            ));
        }
    }
}

SOYShopPlugin::extension("soyshop.site.beforeoutput", "util_multi_language", "UtilMultiLanguageBeforeOutput");

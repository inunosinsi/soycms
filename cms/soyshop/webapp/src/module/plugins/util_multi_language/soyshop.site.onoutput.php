<?php
/*
 * soyshop.site.onoutput.php
 * Created: 2010/03/04
 */

class UtilMultiLanguageOnOutput extends SOYShopSiteOnOutputAction{

	/**
	 * @return string
	 */
	function onOutput($html){
		//canonicalがある場合はhreflangも出力
		preg_match('/<link.*rel=\"canonical\".*href="(.*?)".*?>/', $html, $tmp);
		if(isset($tmp[1])){
			$canonicalTag = $tmp[0];

			SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
			$config = UtilMultiLanguageUtil::getConfig();

			//多言語設定がすでに入っている場合は除く
			$canonicalUrl = SOY2Logic::createInstance("module.plugins.util_multi_language.logic.RedirectLanguageSiteLogic")->getJapanaseUrl($config, $tmp[1]);

			$host = $_SERVER["HTTP_HOST"];

			foreach(UtilMultiLanguageUtil::allowLanguages() as $lang => $title){
				if(!isset($config[$lang])) continue;
				$conf = $config[$lang];
				if(!isset($conf["is_use"]) || (int)$conf["is_use"] !== 1) continue;
				if(isset($conf["prefix"]) && strlen($conf["prefix"])){
					$hreflang = str_replace($host . "/", $host . "/" . $conf["prefix"] . "/", $canonicalUrl);
					$tag = "<link rel=\"alternate\" hreflang=\"" . $lang . "\" href=\"" . $hreflang . "\">";
				}else{
					$tag = "<link rel=\"alternate\" hreflang=\"" . $lang . "\" href=\"" . $canonicalUrl . "\">";
				}

				$canonicalTag .= "\n" . $tag;
			}

			$html = str_replace($tmp[0], $canonicalTag, $html);
		}

		return $html;
	}
}

SOYShopPlugin::extension("soyshop.site.onoutput", "util_multi_languare", "UtilMultiLanguageOnOutput");

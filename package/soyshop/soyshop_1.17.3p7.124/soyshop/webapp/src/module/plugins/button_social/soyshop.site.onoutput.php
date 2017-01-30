<?php
/*
 * soyshop.site.onoutput.php
 * Created: 2010/03/04
 */
include_once(dirname(__FILE__) . "/common.php");
class ButtonSocialOnOutput extends SOYShopSiteOnOutputAction{

	/**
	 * @return string
	 */
	function onOutput($html){
		
		//fb_rootの挿入設定　カートとマイページは無条件で挿入
		if(defined("SOYSHOP_PAGE_ID")){
			SOY2::import("module.plugins.button_social.util.ButtonSocialUtil");
			$displayConfig = ButtonSocialUtil::getPageDisplayConfig();
			if(isset($displayConfig[SOYSHOP_PAGE_ID]) && $displayConfig[SOYSHOP_PAGE_ID] == 0){
				return $html;
			}
		}
		
		$common = new ButtonSocialCommon();
		if(stripos($html, '<body>') !== false){
			$html = str_ireplace('<body>', '<body>' . "\n" . $common->getFbRoot(), $html);
		}elseif(preg_match('/<body\\s[^>]+>/', $html)){
			$html = preg_replace('/(<body\\s[^>]+>)/', "\$0\n" . $common->getFbRoot(), $html);
		}else{
			//何もしない
		}
		
		return $html;
	}
}

SOYShopPlugin::extension("soyshop.site.onoutput", "button_social", "ButtonSocialOnOutput");

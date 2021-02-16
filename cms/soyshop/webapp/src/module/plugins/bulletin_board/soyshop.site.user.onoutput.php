<?php
/**
 * 商品や注文と記述されている個所を掲示板関連の文言に変更
 */
class BulletinBoardUserOutput extends SOYShopSiteUserOnOutputAction{

	/**
	 * @return string
	 */
	function onOutput($html){
		$replacements = array();

		if(!defined("MYPAGE_EXTEND_BOOTSTRAP")){
			if(defined("SOYSHOP_MYPAGE_MODE") && SOYSHOP_MYPAGE_MODE && SOYSHOP_CURRENT_MYPAGE_ID == "bootstrap") define("MYPAGE_EXTEND_BOOTSTRAP", true);
			if(!defined("MYPAGE_EXTEND_BOOTSTRAP")) define("MYPAGE_EXTEND_BOOTSTRAP", false);
		}

		$replacements = array(
			//"購入" => "予約",
		);

		if(!count($replacements)) return $html;

		foreach($replacements as $old => $new){
			$html = str_replace($old, $new, $html);
		}

		return $html;
	}
}

SOYShopPlugin::extension("soyshop.site.user.onoutput", "bulletin_board", "BulletinBoardUserOutput");

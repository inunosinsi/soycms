<?php
/**
 * 商品や注文と記述されている個所を予約関連の文言に変更
 */
class ReserveCalendarUserOutput extends SOYShopSiteUserOnOutputAction{

	/**
	 * @return string
	 */
	function onOutput($html){
		$replacements = array();

		if(SOYSHOP_CART_MODE && SOYSHOP_CURRENT_CART_ID == "bootstrap"){	//カートモード
			$replacements = array(
				"商品番号" => "プランコード",
				"商品" => "予約プラン",
				"個数" => "人数",
				"お買い物を続ける" => "戻る",
				"注文" => "予約",
				"数量" => "人数"
			);
		}else if(SOYSHOP_MYPAGE_MODE && SOYSHOP_CURRENT_MYPAGE_ID == "bootstrap"){
			$replacements = array(
				"購入" => "予約",
				"商品番号" => "プランコード",
				"商品" => "プラン",
				"注文" => "予約",
				"個数" => "人数",
				"お買い物" => "予約"
			);
		}

		if(count($replacements)){
			foreach($replacements as $old => $new){
				$html = str_replace($old, $new, $html);
			}
		}

		return $html;
	}
}

SOYShopPlugin::extension("soyshop.site.user.onoutput", "reserve_calendar", "ReserveCalendarUserOutput");

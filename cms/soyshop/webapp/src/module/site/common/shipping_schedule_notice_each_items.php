<?php

SOY2::import("util.SOYShopPluginUtil");
function soyshop_shipping_schedule_notice_each_items($html, $page){
	$obj = $page->create("soyshop_shipping_schedule_notice_each_items", "HTMLTemplatePage", array(
		"arguments" => array("soyshop_shipping_schedule_notice_each_items", $html)
	));

	if(SOYShopPluginUtil::checkIsActive("parts_shipping_schedule_notice_each_items") && SOYShopPluginUtil::checkIsActive("parts_calendar")){
		$itemId = 0;
		if($page->getPageObject()->getType() == SOYShop_Page::TYPE_DETAIL){
			$itemId = (int)$page->getItem()->getId();
		}

		//商品コードから商品IDを取得
		if(preg_match('/item:code="(.*?)"/', $html, $tmp)){
			if(isset($tmp[1]) && strlen(trim($tmp[1]))){
				$code = trim($tmp[1]);

				//商品詳細表示プラグインと連携している場合
				if($code == "##ALIAS##" && SOYShopPluginUtil::checkIsActive("parts_item_detail")){
					$args = $page->getArguments();
					$code = (isset($args[0])) ? trim($args[0]) : "";
					if(strpos($code, ".html")) $code = str_replace(".html", "", $code);
				}

				if(strlen($code)){
					try{
						$itemId = (int)SOY2DAOFactory::create("shop.SOYShop_ItemDAO")->getByCode($code)->getId();
					}catch(Exception $e){
						//
					}
				}
			}
		}

		if(is_numeric($itemId) && $itemId > 0){
			SOY2::import("module.plugins.parts_shipping_schedule_notice.util.ShippingScheduleUtil");
			SOY2::import("module.plugins.parts_shipping_schedule_notice_each_items.util.ShippingScheduleEachItemsUtil");
			$config = ShippingScheduleEachItemsUtil::getConfig($itemId);

			//非表示設定
			if(!isset($config["hidden"]) || $config["hidden"] != 1){
				//本日の文言を取得
				$bizLogic = SOY2Logic::createInstance("module.plugins.parts_calendar.logic.BusinessDateLogic");

				$idx = "";

				//連休の設定があるか調べる
				if(ShippingScheduleUtil::checkDuringConsecutiveHolidays($config)){
					$idx = ShippingScheduleUtil::HOL_CO;
				}else{
					$now = time();

					//本日が定休日であるか？
					if(!$bizLogic->checkRegularHoliday($now)){	//営業日
						$idx = "biz";
					}else{	//定休日
						$idx = "hol";
					}

					//今が午前であるか？
					if(date("H", $now) < 12){	//午前
						$idx .= "_am";
					}else{	//午後
						$idx .= "_pm";
					}
				}

				if(!isset($config["notice"][$idx])){ //標準テンプレート
					$config = ShippingScheduleEachItemsUtil::getTemplates();
				}

				if(isset($config["notice"][$idx])){
					$wording = $config["notice"][$idx];

					//○日後の出荷
					$after = (isset($config["schedule"][$idx])) ? (int)$config["schedule"][$idx] : 1;

					//指定の日が定休日であるか？定休日であればその次の日に発送
					for(;;){
						if(!$bizLogic->checkRegularHoliday(time() + $after * 24 * 60 * 60)) break;
						$after++;
					}

					//置換文字列後に出力
					echo nl2br(htmlspecialchars(ShippingScheduleUtil::replace($wording, $after), ENT_QUOTES, "UTF-8"));
				}
			}
		}
	}
}

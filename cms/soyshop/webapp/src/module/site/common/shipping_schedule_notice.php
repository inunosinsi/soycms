<?php

SOY2::import("util.SOYShopPluginUtil");
function soyshop_shipping_schedule_notice($html, $page){
	$obj = $page->create("soyshop_shipping_schedule_notice", "HTMLTemplatePage", array(
		"arguments" => array("soyshop_shipping_schedule_notice", $html)
	));

	if(SOYShopPluginUtil::checkIsActive("parts_shipping_schedule_notice") && SOYShopPluginUtil::checkIsActive("parts_calendar")){
		//本日の文言を取得
		$bizLogic = SOY2Logic::createInstance("module.plugins.parts_calendar.logic.BusinessDateLogic");

		//連休を調べる
		SOY2::import("module.plugins.parts_shipping_schedule_notice.util.ShippingScheduleUtil");
		$config = ShippingScheduleUtil::getConfig();

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

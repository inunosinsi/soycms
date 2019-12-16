<?php

class CalendarExpandSmartDownload extends SOYShopDownload{

	function execute(){
		//マイページ用
		header("Content-Type: text/css; charset: UTF-8");
		echo file_get_contents(dirname(__FILE__) . "/css/mypage.css");
		exit;
	}
}
SOYShopPlugin::extension("soyshop.download", "calendar_expand_smart", "CalendarExpandSmartDownload");

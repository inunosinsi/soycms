<?php
/*
 */
class OrderMailInsertTemplateInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=order_mail_insert_template").'">商品毎のメール文面定形文テンプレートの設定</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info", "order_mail_insert_template", "OrderMailInsertTemplateInfo");

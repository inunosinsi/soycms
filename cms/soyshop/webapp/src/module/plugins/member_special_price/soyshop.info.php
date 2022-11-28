<?php
/*
 */
class MemberSpecialPriceInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=member_special_price").'">会員特別価格の項目設定</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info","member_special_price","MemberSpecialPriceInfo");
?>

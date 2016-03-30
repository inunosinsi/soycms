<?php
/*
 */
class CommonUserCustomfieldInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("User.CustomField").'">ユーザカスタムフィールドの追加</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info","common_user_customfield","CommonUserCustomfieldInfo");
?>

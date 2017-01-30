<?php
/*
 */
class CommonCategoryCustomfieldInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Item.Category.Customfield").'">カテゴリカスタムフィールドの追加</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info","common_category_customfield","CommonCategoryCustomfieldInfo");
?>

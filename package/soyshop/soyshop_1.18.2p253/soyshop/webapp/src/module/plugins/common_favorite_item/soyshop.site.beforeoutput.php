<?php
/*
 * soyshop.site.beforeoutput.php
 * Created: 2010/03/11
 */

class CommonFavoriteItemBeforeOutput extends SOYShopSiteBeforeOutputAction{

	function beforeOutput($page){

		//これらの条件を満たさないと処理は開始しない
		if(isset($_GET["favorite"]) && isset($_GET["a"]) && soy2_check_token()){
			
			$favoriteLogic = SOY2Logic::createInstance("module.plugins.common_favorite_item.logic.FavoriteLogic");

			$itemId = (int)$_GET["favorite"];
			
			//商品が存在していることを確認する
			if(!$favoriteLogic->checkItem($itemId)) $this->redirect();
					
			//ログインしているかを調べる
			$userId = $favoriteLogic->getUserId();
			if(!isset($userId)) $this->redirect();
			
			switch($_GET["a"]){
				case "add":
					$favoriteLogic->registerFavorite($itemId, $userId);
					break;
				case "remove":
					$favoriteLogic->cancelFavorite($itemId, $userId);
					break;
			}	
			
			$this->redirect();
		}
	}
	
	function redirect(){
		header("Location:" . $_SERVER["HTTP_REFERER"]);
	}
}
SOYShopPlugin::extension("soyshop.site.beforeoutput","common_favorite_item","CommonFavoriteItemBeforeOutput");
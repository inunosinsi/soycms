<?php

class CommonFavoriteItemBeforeOutput extends SOYShopSiteBeforeOutputAction{

	function beforeOutput(WebPage $page){

		//これらの条件を満たさないと処理は開始しない
		if(isset($_GET["favorite"]) && isset($_GET["a"]) && soy2_check_token()){

			$favLogic = SOY2Logic::createInstance("module.plugins.common_favorite_item.logic.FavoriteLogic");

			$itemId = (int)$_GET["favorite"];

			//商品が存在していることを確認する
			if(!$favLogic->checkItem($itemId)) $this->redirect();

			//ログインしているかを調べる
			$userId = MyPageLogic::getMyPage()->getUserId();
			if($userId === 0) $this->redirect();

			switch($_GET["a"]){
				case "add":
					$favLogic->register($itemId, $userId);
					break;
				case "remove":
					$favLogic->cancel($itemId, $userId);
					break;
			}

			$this->redirect();
		}
	}

	function redirect(){
		header("Location:" . $_SERVER["HTTP_REFERER"]);
	}
}
SOYShopPlugin::extension("soyshop.site.beforeoutput", "common_favorite_item", "CommonFavoriteItemBeforeOutput");

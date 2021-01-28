<?php
class ShoppingMallAuth extends SOYShopAuthBase{

	function auth(){
		if(SOYMALL_SELLER_ACCOUNT) {	//念の為
			$classPath = $this->getClassPath();
			if(strpos($classPath, "Order") !== false){
				//注文の時のみ振る舞いが異なる
				if(($classPath == "Order.IndexPage" || $classPath == "OrderPage") && !AUTH_ORDER) self::_jump();
			}else{
				//出店者であれば絶対に見せないページ
				foreach(array("Review", "Plugin", "Config", "Site") as $t){
					if(is_numeric(strpos($classPath,"Review"))) self::_jump();
				}
				//if(!AUTH_EXTENSION && strpos($classPath,"Extension") === 0) return false;
				//if(!AUTH_ORDER && strpos($classPath,"Order") === 0) return false;
				//if(!AUTH_USER && strpos($classPath,"User") === 0) return false;
			}

			//商品ページの場合
			if(is_numeric(strpos($classPath,"Item"))){
				//一覧ページ、商品情報の修正ページ、もしくは作成ページ以外は表示させない
				$isJump = true;
				foreach(array("Item", "Detail", "Create", "Search") as $t){
					if(is_numeric(strpos($classPath, $t . "Page"))) {
						$isJump = false;
						break;
					}
				}
				if($isJump) self::_jump();
			}

			//注文ページ
			if(is_numeric(strpos($classPath,"Order"))){
				//管理画面からの注文を禁止
				if(is_numeric(strpos($classPath, "Register"))) self::_jump();
			}

			//顧客ページ
			if(is_numeric(strpos($classPath,"User"))){
				//一覧ページ、商品情報の修正ページ、もしくは作成ページ以外は表示させない
				$isJump = true;
				foreach(array("User", "Detail", "Register", "Search") as $t){
					if(is_numeric(strpos($classPath, $t . "Page"))) {
						$isJump = false;
						break;
					}
				}
				if($isJump) self::_jump();
			}
		}
	}

	private function _jump(){
		SOY2PageController::jump("");
	}
}
SOYShopPlugin::extension("soyshop.auth", "shopping_mall", "ShoppingMallAuth");

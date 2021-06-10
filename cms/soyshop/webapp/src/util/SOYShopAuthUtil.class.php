<?php

class SOYShopAuthUtil {

	const AUTH_STORE_OWNER = 20;	//shop App側の出店者の番号←高速化の為に決め打ち

	function __construct(){
		SOY2::import("domain.config.SOYShop_ShopConfig");
	}

	private static function _authes(){
		return array(
			"HOME" => true,
			"EXTENSION" => true,
			"ORDER" => true,
			"ITEM" => true,
			"USER" => true,
			"REVIEW" => true,
			"CONFIG" => true,
			"PLUGIN" => true,
			"SITE" => true,		//ページの作成やテンプレートの編集等
			"OPERATE" => true,	//更新の操作に関するもの,
			"CHANGE" => true,	//更新の操作の内、公開に関するもの
			"ADMINORDER" => true,	//管理画面から注文を追加する
			"CSV" => true, 	// CSVの操作周り
			"SOYAPP" => true,	//SOY InquiryやSOY Mail,
			"IFRAME" => SOYShopPluginUtil::checkIsActive("common_abstract"),	//概要のiframeの表示の有無
			"ABSTRACT" => true	//概要
		);
	}

	public static function setAuthConstant(){
		$authes = self::_authes();

		switch(self::_auth()){
			case 1:	//一般管理者
				//何もしない
				break;
			case 2:	//受注管理者 設定、プラグインとサイト管理を封じる
				$authes["CONFIG"] = false;
				$authes["PLUGIN"] = false;
				$authes["SITE"] = false;
				break;
			case 3:	//管理制限者	更新の操作を封じる
				$authes["CONFIG"] = false;
				$authes["PLUGIN"] = false;
				$authes["SITE"] = false;
				$authes["OPERATE"] = false;
				$authes["CSV"] = false;
				//$authes["CHANGE"] = false;
				$authes["SOYAPP"] = false;
				break;
			case 10:	//商品管理制限者
				foreach($authes as $key => $auth){
					$authes[$key] = false;
				}
				$authes["ITEM"] = true;
				$authes["OPERATE"] = true;
				break;
			case 11:	//商品管理 + CSV
				foreach($authes as $key => $auth){
					$authes[$key] = false;
				}
				$authes["ITEM"] = true;
				$authes["OPERATE"] = true;
				$authes["CSV"] = true;
				break;
			case 20:	//出店者
				foreach($authes as $key => $auth){
					$authes[$key] = false;
				}
				$authes["HOME"] = true;
				$authes["EXTENSION"] = true;
				$authes["ITEM"] = true;
				$authes["ORDER"] = true;	//もしかしたらExtension方式に切り替えるかも
				$authes["USER"] = true;
				$authes["OPERATE"] = true;
				$authes["CHANGE"] = true;
				if(!defined("SOYMALL_SELLER_ACCOUNT")) define("SOYMALL_SELLER_ACCOUNT", true);
				break;
			case 0:	//すべてfalse
			default:
				foreach($authes as $key => $bool){
					$authes[$key] = false;
				}
		}

		//SOYMallモード
		if(!defined("SOYMALL_SELLER_ACCOUNT")) define("SOYMALL_SELLER_ACCOUNT", false);

		//管理画面で設定した内容を反映
		if($authes["ORDER"] || $authes["USER"] || $authes["ITEM"]){
			$config = SOYShop_ShopConfig::load();
			if($authes["ORDER"]) $authes["ORDER"] = $config->getDisplayOrderAdminPage();
			if($authes["USER"]) $authes["USER"] = $config->getDisplayUserAdminPage();
			if($authes["ITEM"]) $authes["ITEM"] = $config->getDisplayItemAdminPage();
		}
		if($authes["REVIEW"]) $authes["REVIEW"] = SOYShopPluginUtil::checkIsActive("item_review");

		//定数の設定
		foreach($authes as $key => $bool){
			if(!defined("AUTH_" . $key)) define("AUTH_" . $key, $bool);
		}

		//soy:display="app_limit_function"の設定
		DisplayPlugin::toggle("app_limit_function", AUTH_OPERATE);
		DisplayPlugin::toggle("app_limit_function_rv", AUTH_OPERATE);	//1ページで二回使用している場合の予備
	}

	//権限を調べ、開いてはいけないページの場合は
	public static function checkAuthEachPage($classPath){
		//トップページのみ特別な処理
		if(!AUTH_HOME && (strpos($classPath, "IndexPage") === 0 || !strlen($classPath))){
			if(AUTH_ORDER) SOY2PageController::jump("Order");
			if(AUTH_ITEM) SOY2PageController::jump("Item");
			if(AUTH_USER) SOY2PageController::jump("User");

			// @ToDo 該当しないページがあった場合はどうしよう？
		}

		if(SOYMALL_SELLER_ACCOUNT){
			//権限周りの拡張ポイント。拡張ポイントの先でリダイレクトを行う
			SOYShopPlugin::load("soyshop.auth");
			SOYShopPlugin::invoke("soyshop.auth", array(
				"classPath" => $classPath
			));
		}else{
			if(!self::_check($classPath)) SOY2PageController::jump("");
		}
	}

	private static function _check($classPath){
		if(strpos($classPath, "Order") !== false){
			//注文の時のみ振る舞いが異なる
			if(($classPath == "Order.IndexPage" || $classPath == "OrderPage") && !AUTH_ORDER) return false;

			//商品管理のみアカウントの場合は注文すべてのページを見れなくする
			if(AUTH_ITEM && !AUTH_EXTENSION && !AUTH_ORDER) return false;
		}else{
			if(!AUTH_EXTENSION && strpos($classPath,"Extension") === 0) return false;
			//if(!AUTH_ORDER && strpos($classPath,"Order") === 0) return false;
			if(!AUTH_ITEM && strpos($classPath,"Item") === 0) return false;
			if(!AUTH_USER && strpos($classPath,"User") === 0) return false;
			if(!AUTH_REVIEW && strpos($classPath,"Review") === 0) return false;
			if(!AUTH_PLUGIN && strpos($classPath,"Plugin") === 0) return false;
			if(!AUTH_SITE && strpos($classPath,"Site") === 0) return false;

			//CSVの場合 CSVに関するページは見れなくする
			if(!AUTH_CSV) {
				if(strpos($classPath, "Import") || strpos($classPath, "Export")) return false;
			}

			//CONFIGの場合、設定に関するページは見れなくする
			if(!AUTH_CONFIG) {
				if(is_numeric(strpos($classPath,"Config")) || strpos($classPath, "Customfield") || strpos($classPath, "Setting")) return false;
			}
		}

		//何もなければtrue
		return true;
	}

	public static function getAuth(){
		return self::_auth();
	}

	private static function _auth(){
		static $auth;
		if(is_null($auth)){
			$session = SOY2ActionSession::getUserSession();
			if($session->getAttribute("isdefault")){	//一般管理者として扱う
				$auth = 1;
			}else{
				$auth = (int)$session->getAttribute("app_shop_auth_level");
			}
		}
		return $auth;
	}
}

<?php

class BulletinBoardMypage extends SOYShopMypageBase{

	function getTitleFormat(){
		//アプリ名
		$appName = SOYShop_DataSets::get("config.mypage.title", "マイページ");
		$format = $appName;

		$args = self::_args();
		if(!count($args)) return $format;

		switch($args[0]){
			case "topic":
				if(isset($args[1]) && is_numeric($args[1])){ //トピックトップ
					$group = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.GroupLogic")->getById($args[1]);
					$format = $group->getName() . "の掲示板 - " . $appName;
				} else {
					switch($args[1]){
						case "detail":
							if(isset($args[2]) && is_numeric($args[2])){
								$topic = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.TopicLogic")->getById($args[2], true);
								$group = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.GroupLogic")->getById($topic->getGroupId());
								$format = $topic->getLabel() . " - " . $group->getName() . "の掲示板 - " . $appName;
							}
							break;
						case "confirm":
							$format = "投稿内容の確認 - " . $appName;
							break;
						case "complete":
							$format = "投稿完了 - " . $appName;
							break;
						default:
							if(isset($args[1]) && is_numeric($args[1])){
								$topic = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.TopicLogic")->getById($args[1], true);
								$group = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.GroupLogic")->getById($topic->getGroupId());
								$format = $topic->getLabel() . " - " . $group->getName() . "の掲示板 - " . $appName;
							}
							break;
					}
				}
				break;
			case "user":
			default:
				//今の所何もしない
				break;
		}

		return $format;
	}

	private function _args(){
		$uri = rtrim($_SERVER["PATH_INFO"], "/");
		$uri = str_replace("/" . SOYSHOP_ID . "/", "/", $uri);
		$uri = str_replace("/" . SOYShop_DataSets::get("config.mypage.url") . "/", "", $uri);
		$args = explode("/", $uri);
		if(count($args) && $args[0] == "board") array_shift($args);
		return $args;
	}

	function getCanonicalUrl(){
		$uri = soyshop_get_mypage_url(true);
		$reqUri = $_SERVER["REQUEST_URI"];
		$res = strpos($reqUri, "/". SOYSHOP_ID . "/");
		if(is_numeric($res) && $res === 0){
			$reqUri = ltrim($reqUri, "/");
			$reqUri = substr($reqUri, strpos($reqUri, "/"));
		}

		//mypageのuriを除く
		$mypageUri = soyshop_get_mypage_uri();
		$res = strpos($reqUri, "/". $mypageUri . "/");
		if(is_numeric($res) && $res === 0){
			$reqUri = ltrim($reqUri, "/");
			$reqUri = substr($reqUri, strpos($reqUri, "/"));
		}

		$uri .= "/" . ltrim($reqUri, "/");
		$uri = rtrim($uri , "/");

		//トライリングスラッシュの設定
		if((SOYShop_ShopConfig::load()->getIsTrailingSlash() == 1)) $uri .= "/";
		return $uri;
	}

	private function _getMypageUri(){
		// $url = ;
		// preg_match('/^http.*?:\/\//', $url, $tmp);
		// if(isset($tmp[0])){
		// 	$url = substr($url, strpos($url, "://") + 3);
		// 	$url = substr($url, strpos($url, "/"));
		// }
		// return str_replace("/" . SOYSHOP_ID . "/", "/", $url . "/board");
	}
}
SOYShopPlugin::extension("soyshop.mypage", "bulletin_board", "BulletinBoardMypage");

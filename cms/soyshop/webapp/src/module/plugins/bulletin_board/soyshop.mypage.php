<?php

class BulletinBoardMypage extends SOYShopMypageBase{

	function getTitleFormat(){
		//アプリ名
		$appName = SOYShop_DataSets::get("config.mypage.title", "マイページ");
		$format = $appName;

		$args = self::_args();
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
		return explode("/", $uri);
	}
}
SOYShopPlugin::extension("soyshop.mypage", "bulletin_board", "BulletinBoardMypage");

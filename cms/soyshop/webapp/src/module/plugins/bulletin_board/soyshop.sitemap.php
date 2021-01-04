<?php

class BulletinBoardSitemap extends SOYShopSitemapBase{

	function __construct(){}

	function items(){
		$items = array();

		$uri = str_replace("/" . SOYSHOP_ID . "/", "/", soyshop_get_mypage_url() . "/board");
		$items[] = array("loc" => $uri, "priority" => "0.8", "lastmod" => time());

		/**
		 * @ToDo lastmod
		 * ページャ
		 **/

		//グループ
		$groups = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.GroupLogic")->get();
		if(count($groups)){
			foreach($groups as $group){
				$items[] = array("loc" => $uri . "/topic/" . $group->getId(), "priority" => "0.5", "lastmod" => time());
			}
		}

		//トピック詳細
		$topics = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.TopicLogic")->get(true);
		if(count($topics)){
			foreach($topics as $topic){
				$items[] = array("loc" => $uri . "/topic/detail/" . $topic->getId(), "priority" => "0.5", "lastmod" => time());
			}
		}

		//ユーザ
		$items[] = array("loc" => $uri . "/user/", "priority" => "0.5", "lastmod" => time());
		$users = self::_getUsers();
		if(count($users)){
			foreach($users as $user){
				$items[] = array("loc" => $uri . "/user/detail/" . $user->getId(), "priority" => "0.5", "lastmod" => time());
			}
		}

		return $items;
	}

	private function _getUsers(){
		try{
			return SOY2DAOFactory::create("user.SOYShop_UserDAO")->getISpublishUsers();
		}catch(Exception $e){
			return array();
		}
	}
}
SOYShopPlugin::extension("soyshop.sitemap", "bulletin_board", "BulletinBoardSitemap");

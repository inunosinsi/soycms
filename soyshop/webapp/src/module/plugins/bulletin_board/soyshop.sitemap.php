<?php

class BulletinBoardSitemap extends SOYShopSitemapBase{

	function __construct(){
		SOY2::import("module.plugins.bulletin_board.domain.SOYBoard_PostDAO");
	}

	function items(){
		$postDao = SOY2DAOFactory::create("SOYBoard_PostDAO");

		$items = array();

		$uri = self::_getMypageUri();

		//lastmodは最後の投稿の時刻を得る
		$lastmod = $postDao->getLastPostDate();
		if(is_null($lastmod)) $lastmod = time();
		$items[] = array("loc" => $uri, "priority" => "0.8", "lastmod" => $lastmod);

		/**
		 * @ToDo lastmod
		 * ページャ
		 **/

		//グループ
		$groups = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.GroupLogic")->get();
		if(count($groups)){
			foreach($groups as $group){
				$lastmod = $postDao->getLatestPostByGroupId($group->getId())->getCreateDate();
				if(is_null($lastmod)) $lastmod = $group->getUpdateDate();
				$items[] = array("loc" => $uri . "/topic/" . $group->getId(), "priority" => "0.5", "lastmod" => $lastmod);
			}
		}

		//トピック詳細
		$topics = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.TopicLogic")->get(true);
		if(count($topics)){
			foreach($topics as $topic){
				$lastmod = $postDao->getLatestPostByTopicId($topic->getId())->getCreateDate();
				$items[] = array("loc" => $uri . "/topic/detail/" . $topic->getId(), "priority" => "0.5", "lastmod" => $lastmod);
			}
		}

		//ユーザ
		$users = self::_getUsers();
		if(count($users)){
			$lastmod = 0;
			foreach($users as $user){
				$items[] = array("loc" => $uri . "/user/detail/" . $user->getId(), "priority" => "0.5", "lastmod" => $user->getUpdateDate());
				if($lastmod < $user->getUpdateDate()) $lastmod = $user->getUpdateDate();
			}
			$items[] = array("loc" => $uri . "/user/", "priority" => "0.5", "lastmod" => $lastmod);
		}

		return $items;
	}

	private function _getMypageUri(){
		$url = soyshop_get_mypage_url();
		preg_match('/^http.*?:\/\//', $url, $tmp);
		if(isset($tmp[0])){
			$url = substr($url, strpos($url, "://") + 3);
			$url = substr($url, strpos($url, "/"));
		}
		return str_replace("/" . SOYSHOP_ID . "/", "/", $url . "/board");
	}

	private function _getUsers(){
		try{
			return SOY2DAOFactory::create("user.SOYShop_UserDAO")->getIsPublishUsers();
		}catch(Exception $e){
			return array();
		}
	}
}
SOYShopPlugin::extension("soyshop.sitemap", "bulletin_board", "BulletinBoardSitemap");

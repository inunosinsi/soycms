<?php

class BulletinBoardBeforeOutput extends SOYShopSiteBeforeOutputAction{

	function beforeOutput($page){
		$pageObj = $page->getPageObject();

		//カートページとマイページでは読み込まない
		if(!is_object($pageObj) || get_class($pageObj) != "SOYShop_Page") return;

		//sitemap.xmlでない場合は読み込まない
		if(!preg_match('/news.xml/', $pageObj->getUri())) return;

		//フリーページ以外では読み込まない
		if($pageObj->getType() != SOYShop_Page::TYPE_FREE) return;

		SOY2::import("domain.config.SOYShop_ShopConfig");
		$cnf = SOYShop_ShopConfig::load();

		$url = soyshop_get_mypage_url(true) . "/board/";

		header("Content-Type: text/xml");

		$html = array();
		$html[] = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
		$html[] = "<rss version=\"2.0\">";
		$html[] = "	<channel>";
		$html[] = "		<title>" . $cnf->getShopName() . "</title>";
		$html[] = "		<link>" . $url . "</link>";
		$html[] = "		<description></description>";
		$html[] = "	</channel>";


		//新着情報 とりあえず1週間以内の投稿
		$postLogic = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.PostLogic");
		$posts = $postLogic->getNewPosts();
		$groupNames = array();
		if(count($posts)){
			$topicLogic = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.TopicLogic");
			$groupLogic = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.GroupLogic");
			foreach($posts as $post){
				$topic = $topicLogic->getById($post->getTopicId(), true);
				if(!is_numeric($topic->getId())) continue;
				$postCnt = $postLogic->countPostByTopicId($topic->getId());
				if(!isset($groupNames[$topic->getGroupId()])) $groupNames[$topic->getGroupId()] = $groupLogic->getById($topic->getGroupId())->getName();
				$html[] = self::_buildItem($topic->getLabel() . " - " . $groupNames[$topic->getGroupId()] . "の掲示板(" . $postCnt . ")", $url . "topic/detail/" . $topic->getId() . "#" . $post->getId(), "", $post->getCreateDate());
			}
		}

		$html[] = "</rss>";


		$page->addLabel("news.xml", array(
			//"html" => "",
			"html" => implode("\n", $html),
			"soy2prefix" => SOYSHOP_SITE_PREFIX
		));
	}

	private function _buildItem($title, $url, $description, $pubDate){
		$html[] = "	<item>";
		$html[] = "		<title>" . $title. "</title>";
		$html[] = "		<link>" . $url . "</link>";
		if(strlen($description)) $html[] = "		<description>" . $description . "</description>";
		$html[] = "		<pubDate>" . date("r", $pubDate) . "</pubDate>";
		$html[] = "	</item>";
		return implode("\n", $html);
	}
}

SOYShopPlugin::extension("soyshop.site.beforeoutput", "bulletin_board", "BulletinBoardBeforeOutput");

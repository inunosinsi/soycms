<?php

class TagCloudSitemap extends SOYShopSitemapBase{

	private $dao;

	function __construct(){}

	// array(array("loc" => "uri" => "priority" => "0.8", lastmod => "timestamp"))
	function items(){
		SOY2::import("module.plugins.tag_cloud.util.TagCloudUtil");
		$pageId = TagCloudUtil::getPageIdSettedTagCloud();
		if(!is_numeric($pageId) || $pageId == 0) return array();


		$this->dao = new SOY2DAO();

		$results = self::_get();
		if(!count($results)) return array();

		//ワードIDに紐付いた商品で最も新しい更新日のものを取得する
		$wordIds = array();
		foreach($results as $res){
			$wordIds[] = (int)$res["id"];
		}

		$udateList = self::_getUpdateDateListByWordIds($wordIds);
		if(!count($udateList)) return array();

		$page = soyshop_get_page_object($pageId);
		$uri = rtrim($page->getUri(), "/") . "/";


		$lim = $page->getObject()->getLimit();
		$logic = SOY2Logic::createInstance("module.plugins.tag_cloud.logic.TagCloudBlockItemLogic");

		$items = array();

		foreach($results as $res){
			// wordId($res["id"])に紐付いた商品で一番更新日が新しいものをlastmodにする
			if(!isset($udateList[$res["id"]])) continue;

			$tagUri = $uri . rawurlencode($res["word"]);

			$items[] = array(
				"loc" => $tagUri,
				"priority" => "0.5",
				"lastmod" => $udateList[$res["id"]]
			);

			//ページャがあるか？
			$total = $logic->getTotal(TagCloudUtil::getWordIdByAlias($res["word"]));
			$div = (int)ceil($total / $lim);
			if($div < 2) continue;

			for($i = 2; $i <= $div; $i++){
				$items[] = array(
					"loc" => $tagUri . "/page-" . $i . ".html",
					"priority" => "0.3",
					"lastmod" => $udateList[$res["id"]]
				);
			}
		}


		return $items;
	}

	private function _get(){
		$now = time();

		//タグを設定した記事が公開であること。記事が任意のラベルと紐付いていること
		try{
			return $this->dao->executeQuery(
				"SELECT dic.id, dic.word, COUNT(lnk.word_id) AS word_id_count FROM soyshop_tag_cloud_linking lnk ".
				"INNER JOIN soyshop_tag_cloud_dictionary dic ".
				"ON lnk.word_id = dic.id ".
				"INNER JOIN soyshop_item item ".
				"ON lnk.item_id = item.id ".
				"WHERE item.item_is_open = 1 ".
				"AND item.is_disabled = 0 ".
				"AND item.open_period_start <= " . $now . " ".
				"AND item.open_period_end >= " . $now . " ".
				"GROUP BY lnk.word_id ".
				"HAVING COUNT(lnk.word_id) > 0 "
			);
		}catch(Exception $e){
			return array();
		}
	}

	private function _getUpdateDateListByWordIds(array $ids){
		$now = time();

		try{
			$results = $this->dao->executeQuery(
				"SELECT lnk.word_id, MAX(item.update_date) AS max_update_date FROM soyshop_tag_cloud_linking lnk ".
				"INNER JOIN soyshop_item item ".
				"ON lnk.item_id = item.id ".
				"WHERE lnk.word_id IN (" . implode(",", $ids) . ") ".
				"AND item.item_is_open = 1 ".
				"AND item.is_disabled = 0 ".
				"AND item.open_period_start <= " . $now . " ".
				"AND item.open_period_end >= " . $now . " ".
				"GROUP BY lnk.word_id "
			);
		}catch(Exception $e){
			$results = array();
		}
		if(!count($results)) return array();

		$list = array();
		foreach($results as $res){
			$list[(int)$res["word_id"]] = (int)$res["max_update_date"];
		}
		return $list;
	}
}
SOYShopPlugin::extension("soyshop.sitemap", "tag_cloud", "TagCloudSitemap");

<?php

class TagCloudClassifiedWordListComponent extends HTMLList {

	private $randomMode;
	private $count;

	protected function populateItem($entity){
		$words = array();
		$ranks = array();		//array(word_id => count)

		$url = self::_url();
		$rankDivide = 1;

		$categoryId = (is_numeric($entity)) ? (int)$entity : 0;
		if($categoryId > 0){
			//表示速度の改善の為にここでランクの区切りの位を取得する
			$cnf = TagCloudUtil::getConfig();
			if(isset($cnf["divide"]) && (int)$cnf["divide"]) $rankDivide = (int)$cnf["divide"];

			$dao = new SOY2DAO();

			$now = time();

			//タグを設定した記事が公開であること。記事が任意のラベルと紐付いていること
			$sql = "SELECT lnk.word_id, dic.word, dic.hash, COUNT(lnk.word_id) AS word_id_count FROM soyshop_tag_cloud_linking lnk ".
					"INNER JOIN soyshop_tag_cloud_dictionary dic ".
					"ON lnk.word_id = dic.id ".
					"INNER JOIN soyshop_item item ".
					"ON lnk.item_id = item.id ".
					"WHERE dic.category_id = " . $categoryId . " ".
					"AND item.item_is_open = 1 ".
					"AND item.is_disabled = 0 ".
					"AND item.open_period_start <= " . $now . " ".
					"AND item.open_period_end >= " . $now . " ".
					"GROUP BY lnk.word_id ".
					"HAVING COUNT(lnk.word_id) > 0 ";
			//ランダム表示であるか？
			if(is_bool($this->randomMode) && $this->randomMode){
				if(SOY2DAOConfig::type() == "mysql"){
					$sql .= "ORDER BY Rand() ";
				}else{
					$sql .= "ORDER BY Random() ";
				}
			}else{
				$sql .= "ORDER BY word_id_count DESC ";
			}

			//タグの表示個数
			if(is_numeric($this->count) && $this->count > 0){
				$sql .= "LIMIT " . $this->count;
			}

			try{
				$words = $dao->executeQuery($sql);
			}catch(Exception $e){
				//
			}

			//ページのURLを調べる
			if(count($words)){
				// 多言語化
				if((defined("SOYSHOP_PUBLISH_LANGUAGE") && SOYSHOP_PUBLISH_LANGUAGE != "jp")){
					$words = SOY2Logic::createInstance("module.plugins.tag_cloud.logic.MultilingualLogic")->translate($words);
				}

				//ワードID毎の記事数
				$list = array();
				foreach($words as $word){
					if(isset($word["word_id_count"]) && is_numeric($word["word_id_count"]) && (int)$word["word_id_count"] > 0){
						$list[(int)$word["word_id"]] = (int)$word["word_id_count"];
					}
				}
				if(count($list)){
					arsort($list);
					$c = 0;
					foreach($list as $wordId => $cnt){
						$ranks[$wordId] = TagCloudUtil::getRank($c++);
					}
				}
			}
		}

		$this->addLabel("label", array(
			"soy2prefix" => "cms",
			"text" => ($categoryId > 0) ? self::_label($categoryId) : null
		));

		//タグクラウド一覧
		SOY2::import("module.plugins.tag_cloud.component.TagCloudWordListComponent");
		$this->createAdd("tag_cloud_word_list", "TagCloudWordListComponent", array(
			"soy2prefix" => "block",
			"list" => $words,
			"url" => $url,
			"ranks" => $ranks
		));
	}

	private function _label(int $categoryId){
		static $dao;
		if(is_null($dao)) {
			SOY2::import("module.plugins.tag_cloud.domain.SOYShop_TagCloudCategoryDAO");
			$dao = SOY2DAOFactory::create("SOYShop_TagCloudCategoryDAO");
		}
		try{
			return $dao->getById($categoryId)->getLabel();
		}catch(Exception $e){
			return null;
		}
	}

	private function _url(){
		static $url;
		if(is_null($url)){
			SOY2::import("module.plugins.tag_cloud.util.TagCloudUtil");
			$pageId = TagCloudUtil::getPageIdSettedTagCloud();
			$url = soyshop_get_page_url(soyshop_get_page_object($pageId)->getUri());
		}
		return $url;
	}

	function setRandomMode($randomMode){
		$this->randomMode = $randomMode;
	}
	function setCount($count){
		$this->count = $count;
	}
}

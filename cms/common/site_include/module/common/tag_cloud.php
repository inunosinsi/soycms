<?php
function soycms_tag_cloud($html, $page){

	$obj = $page->create("tag_cloud", "HTMLTemplatePage", array(
		"arguments" => array("tag_cloud", $html)
	));

	$words = array();
	$url = null;
	$rankDivide = 0;

	//プラグインがアクティブかどうか？
	if(file_exists(_SITE_ROOT_ . "/.plugin/TagCloud.active")){
		//タグクラウドブロックを設置しているページを調べる
		$sql = "SELECT page_id, object FROM Block WHERE class = :blk";

		$dao = new SOY2DAO();
		try{
			$res = $dao->executeQuery($sql, array(":blk" => "PluginBlockComponent"));
		}catch(Exception $e){
			$res = array();
		}

		if(count($res)){
			$pageId = null;
			foreach($res as $v){
				if(strpos($v["object"], "TagCloud")){
					$pageId = (int)$v["page_id"];
					break;
				}
			}

			if(isset($pageId) && is_numeric($pageId)){
				SOY2::import("site_include.plugin.soycms_search_block.util.PluginBlockUtil");
				$labelId = PluginBlockUtil::getLabelIdByPageId($pageId);
				if(isset($labelId) && is_numeric($labelId)){
					//表示速度の改善の為にここでランクの区切りの位を取得する
					SOY2::import("site_include.plugin.tag_cloud.util.TagCloudUtil");
					$cnf = TagCloudUtil::getConfig();
					$rankDivide = (isset($cnf["divide"]) && (int)$cnf["divide"]) ? (int)$cnf["divide"] : 0;

					//タグの表示個数
					$cnt = TagCloudUtil::getDisplayCount($html);

					//タグを設定した記事が公開であること。記事が任意のラベルと紐付いていること
					$sql = "SELECT lnk.word_id, dic.word, COUNT(lnk.word_id) AS word_id_count FROM TagCloudLinking lnk ".
							"INNER JOIN TagCloudDictionary dic ".
							"ON lnk.word_id = dic.id ".
							"INNER JOIN Entry ent ".
							"ON lnk.entry_id = ent.id ".
							"INNER JOIN EntryLabel lab ".
				        	"ON lnk.entry_id = lab.entry_id ".
							"WHERE ent.isPublished = 1 ".
				        	"AND ent.openPeriodEnd >= :now ".
				        	"AND ent.openPeriodStart < :now ".
							"AND lab.label_id = :labelId ".
							"GROUP BY lnk.word_id ".
							"HAVING COUNT(lnk.word_id) > 0 ".
							"ORDER BY word_id_count DESC ";

					if(isset($cnt) && is_numeric($cnt) && $cnt > 0){
						$sql .= "LIMIT " . $cnt;
					}

					try{
						$words = $dao->executeQuery($sql, array(":now" => time(), ":labelId" => $labelId));
					}catch(Exception $e){
						//
					}

					//ページのURLを調べる
					if(count($words)){
						$url = SOY2DAOFactory::create("cms.SiteConfigDAO")->get()->getConfigValue("url");
						if(is_null($url)) $url = CMSPageController::createLink("", true);

						try{
							$uri = SOY2DAOFactory::create("cms.PageDAO")->getById($pageId)->getUri();
							$url .= $uri;
						}catch(Exception $e){
							//
						}
					}
				}
			}
		}
	}

	//タグクラウド一覧
	$obj->createAdd("tag_cloud_word_list", "TagCloudWordListComponent", array(
		"soy2prefix" => "p_block",
		"list" => $words,
		"url" => $url,
		"divide" => $rankDivide
	));

	$obj->display();
}

class TagCloudWordListComponent extends HTMLList {

	private $url;
	private $divide; //タグの使用頻度が何位でクラスのrank01を次の数字にするか？

	protected function populateItem($entity, $key, $int){
		$this->addLink("tag_link", array(
			"soy2prefix" => "cms",
			"link" => (isset($entity["word_id"]) && is_numeric($entity["word_id"])) ? $this->url . "?tagcloud=" . $entity["word_id"] : "",
			"attr:class" => self::_getRankClass($int)
		));

		$this->addLabel("tag", array(
			"soy2prefix" => "cms",
			"text" => (isset($entity["word"])) ? $entity["word"] : ""
		));
	}

	private function _getRankClass($int){
		static $rank;
		if(is_null($rank)) $rank = 1;
		$cls = self::_buildClass($rank);
		if($int % $this->divide === 0) $rank++;
		return $cls;
	}

	private function _buildClass($rank){
		if(strlen($rank) === 1) $rank = "0" . $rank;
		return "tagcloud rank" . $rank;
	}

	function setUrl($url){
		$this->url = $url;
	}
	function setDivide($divide){
		$this->divide = $divide;
	}
}

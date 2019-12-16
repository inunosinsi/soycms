<?php
function soycms_tag_cloud($html, $page){

	$obj = $page->create("tag_cloud", "HTMLTemplatePage", array(
		"arguments" => array("tag_cloud", $html)
	));

	$words = array();
	$url = null;

	//プラグインがアクティブかどうか？
	if(file_exists(_SITE_ROOT_ . "/.plugin/TagCloud.active")){
		//タグクラウドブロックを設置しているページを調べる
		$sql = "SELECT page_id FROM Block ".
				"WHERE class = :blk ".
				"AND object LIKE :obj ".
				"LIMIT 1;";

		$dao = new SOY2DAO();
		try{
			$res = $dao->executeQuery($sql, array(":blk" => "PluginBlockComponent", ":obj" => "%TagCloud%"));
		}catch(Exception $e){
			$res = array();
		}

		if(isset($res[0]["page_id"])){
			$pageId = (int)$res[0]["page_id"];

			SOY2::import("site_include.plugin.soycms_search_block.util.PluginBlockUtil");
			$labelId = PluginBlockUtil::getLabelIdByPageId($pageId);
			if(isset($labelId) && is_numeric($labelId)){
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

	//タグクラウド一覧
	$obj->createAdd("tag_cloud_word_list", "TagCloudWordListComponent", array(
		"soy2prefix" => "p_block",
		"list" => $words,
		"url" => $url
	));

	$obj->display();
}

class TagCloudWordListComponent extends HTMLList {

	private $url;

	protected function populateItem($entity){
		$this->addLink("tag_link", array(
			"soy2prefix" => "cms",
			"link" => (isset($entity["word_id"]) && is_numeric($entity["word_id"])) ? $this->url . "?tagcloud=" . $entity["word_id"] : ""
		));

		$this->addLabel("tag", array(
			"soy2prefix" => "cms",
			"text" => (isset($entity["word"])) ? $entity["word"] : ""
		));
	}

	function setUrl($url){
		$this->url = $url;
	}
}

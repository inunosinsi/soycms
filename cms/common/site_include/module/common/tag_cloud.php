<?php
function soycms_tag_cloud($html, $page){

	$obj = $page->create("tag_cloud", "HTMLTemplatePage", array(
		"arguments" => array("tag_cloud", $html)
	));

	$words = array();
	$ranks = array();		//array(word_id => count)

	$url = null;
	$rankDivide = 1;

	//プラグインがアクティブかどうか？
	if(file_exists(_SITE_ROOT_ . "/.plugin/TagCloud.active")){
		//タグクラウドブロックを設置しているページを調べる
		SOY2::import("site_include.plugin.tag_cloud.util.TagCloudUtil");
		$pageId = TagCloudUtil::getPageIdSettedTagCloudBlock();

		if(isset($pageId) && is_numeric($pageId)){
			SOY2::import("site_include.plugin.soycms_search_block.util.PluginBlockUtil");
			$labelId = PluginBlockUtil::getLabelIdByPageId($pageId);
			if(isset($labelId) && is_numeric($labelId)){
				//表示速度の改善の為にここでランクの区切りの位を取得する
				$cnf = TagCloudUtil::getConfig();
				if(isset($cnf["divide"]) && (int)$cnf["divide"]) $rankDivide = (int)$cnf["divide"];

				$dao = new SOY2DAO();

				//タグを設定した記事が公開であること。記事が任意のラベルと紐付いていること
				$sql = "SELECT lnk.word_id, dic.word, dic.hash, COUNT(lnk.word_id) AS word_id_count FROM TagCloudLinking lnk ".
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
						"HAVING COUNT(lnk.word_id) > 0 ";
				//ランダム表示であるか？
				if(TagCloudUtil::isRandomMode($html)){
					if(SOY2DAOConfig::type() == "mysql"){
						$sql .= "ORDER BY Rand() ";
					}else{
						$sql .= "ORDER BY Random() ";
					}
				}else{
					$sql .= "ORDER BY word_id_count DESC ";
				}

				//タグの表示個数
				$cnt = TagCloudUtil::getDisplayCount($html);
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
					$url = TagCloudUtil::getUrlByPageId($pageId);

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
		}
	}

	//タグクラウド一覧
	SOY2::import("site_include.plugin.tag_cloud.component.TagCloudWordListComponent");
	$obj->createAdd("tag_cloud_word_list", "TagCloudWordListComponent", array(
		"soy2prefix" => "p_block",
		"list" => $words,
		"url" => $url,
		"ranks" => $ranks
	));

	$obj->display();
}

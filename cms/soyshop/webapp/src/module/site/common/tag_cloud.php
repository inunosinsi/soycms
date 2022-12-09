<?php
function soyshop_tag_cloud($html, $page){

	$obj = $page->create("tag_cloud", "HTMLTemplatePage", array(
		"arguments" => array("tag_cloud", $html)
	));

	$words = array();
	$ranks = array();		//array(word_id => count)

	$url = null;
	$rankDivide = 1;

	//プラグインがアクティブかどうか？
	SOY2::import("util.SOYShopPluginUtil");
    if(SOYShopPluginUtil::checkIsActive("tag_cloud")){

		//タグクラウドブロックを設置しているページを調べる
		SOY2::import("module.plugins.tag_cloud.util.TagCloudUtil");
		$pageId = TagCloudUtil::getPageIdSettedTagCloud();

		if(isset($pageId) && is_numeric($pageId)){
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
					"WHERE item.item_is_open = 1 ".
					"AND item.is_disabled = 0 ".
					"AND item.open_period_start <= " . $now . " ".
					"AND item.open_period_end >= " . $now . " ".
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
				$words = $dao->executeQuery($sql);
			}catch(Exception $e){
				//
			}

			//ページのURLを調べる
			if(count($words)){
				$url = soyshop_get_page_url(soyshop_get_page_object($pageId)->getUri());

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

	//タグクラウド一覧
	SOY2::import("module.plugins.tag_cloud.component.TagCloudWordListComponent");
	$obj->createAdd("tag_cloud_word_list", "TagCloudWordListComponent", array(
		"soy2prefix" => "block",
		"list" => $words,
		"url" => $url,
		"ranks" => $ranks
	));

	$obj->display();
}

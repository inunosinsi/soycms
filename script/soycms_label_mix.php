<?php

function soycms_label_mix(){
	
	//ラベルIDを取得とデータベースから記事の取得件数指定
	$labelIds = array(6, 7, 9, 10);

	//ソート順を調べるために使う
	$labelId = 5;
	$count = 3;

	$entryDao = SOY2DAOFactory::create("cms.EntryDAO");

	//先にinfoの記事IDと更新時間のリストを取得してくる
	$sql = "SELECT ent.id FROM Entry ent ".
		"JOIN EntryLabel lab ".
		"ON ent.id = lab.entry_id ".
		"WHERE ent.openPeriodStart < " . time() . " ".
		"AND ent.openPeriodEnd >= " .time() . " ".
		"AND ent.isPublished = " . Entry::ENTRY_ACTIVE . " ".
		"AND lab.label_id = :labelId ".
		"GROUP BY ent.id ".
		"ORDER BY lab.display_order ASC, ent.cdate DESC";

	/**
	 * @ToDo ソートを調べるために、全件調べる必要はないので、LIMITで上記何件かを指定すると表示速度は向上する
	 */
		
	$binds = array(":labelId" => $labelId);
			
	try{
		$res = $entryDao->executeQuery($sql, $binds);
	}catch(Exception $e){
		$res = array();
	}

	if(!count($res)) return array();

	$sql = "SELECT ent.* FROM Entry ent ".
		"JOIN EntryLabel lab ".
		"ON ent.id = lab.entry_id ".
		"WHERE ent.id = :entryId ".
		"AND ent.openPeriodStart < " . time() . " ".
		"AND ent.openPeriodEnd >= " .time() . " ".
		"AND ent.isPublished = " . Entry::ENTRY_ACTIVE . " ".
		"AND lab.label_id IN (".implode(",", $labelIds).") ";
	$entries = array();
	foreach($res as $v){
		if($count === 0) break;
		try{
			$res = $entryDao->executeQuery($sql, array(":entryId" => $v["id"]));
		}catch(Exception $e){
			continue;
		}
		
		if(isset($res[0]["id"]) && strlen($res[0]["id"])) {
			$entries[] = $entryDao->getObject($res[0]);
			$count--;
		}
	}

	return $entries;
}
?>

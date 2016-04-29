<?php
/**
 * 質問 検索結果
 */
function entry_search(){
	//ブログに設定しているラベルIDはベタ打ち
	$blogLabelId = 1;
	
	if(!isset($_GET["q"]) || strlen(trim($_GET["q"])) === 0) return array();
	$query = htmlspecialchars(trim($_GET["q"]), ENT_QUOTES, "UTF-8");
	
	$sql = "SELECT * FROM Entry entry ".
			"INNER JOIN EntryLabel label ".
			"ON entry.id = label.entry_id ".
			"INNER JOIN EntryAttribute attr ".
			"ON entry.id = attr.entry_id ".
			"WHERE label.label_id = :label_id ".
			"AND (entry.title LIKE :query OR entry.content LIKE :query OR entry.more LIKE :query) ".
			"AND entry.isPublished = 1 ".
			"AND entry.openPeriodEnd >= :now ".
			"AND entry.openPeriodStart < :now ".
			"ORDER BY entry.cdate desc ";
			
	$binds = array(
			":label_id" => $blogLabelId,
			":query" => "%" . $query . "%",
			":now" => time()
	);
		
	$dao = SOY2DAOFactory::create("cms.EntryDAO");
	
	try{
		$results = $dao->executeQuery($sql, $binds);
	}catch(Exception $e){
		return array();
	}
	
	$soycms_search_result = array();
	foreach($results as $key => $row){
		if(isset($row["id"]) && (int)$row["id"]){
			$soycms_search_result[$row["id"]] = $dao->getObject($row);
		}
	}
	
	return $soycms_search_result;
}
?>
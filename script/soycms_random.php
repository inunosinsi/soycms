<?php

/**
 * @usage
 * テンプレートに記述の際、ラベルID(cms:label)と表示件数(cms:count)を指定します。
 * どちらとも指定なしも可能です。
 * <!-- block:id="***" cms:label="1" cms:count="5" -->
 * <div cms:id="title">記事タイトル</div>
 * <!-- /block:id="***" -->
 */
function soycms_random(){
	$pageId = (int)$_SERVER["SOYCMS_PAGE_ID"];

	//ブログページか調べる
	$template = "";
	try{
		$blog = SOY2DAOFactory::create("cms.BlogPageDAO")->getById($pageId);
		$uri = str_replace("/" . $_SERVER["SOYCMS_PAGE_URI"] . "/", "", $_SERVER["PATH_INFO"]);

		//トップページ
		if($uri === (string)$blog->getTopPageUri()){
			$template = $blog->getTopTemplate();
		//アーカイブページ		
		}else if(strpos($uri, $blog->getCategoryPageUri()) === 0 || strpos($uri, $blog->getMonthPageUri()) === 0){
			$template = $blog->getArchiveTemplate();
		//記事ごとページ
		}else{
			$template = $blog->getEntryTemplate();
		}
	}catch(Exception $e){
		try{
			$template = SOY2DAOFactory::create("cms.PageDAO")->getById($pageId)->getTemplate();
		}catch(Exception $e){
			return array();
		}
	}

	try{
		$blocks = SOY2DAOFactory::create("cms.BlockDAO")->getByPageId($pageId);
	}catch(Exception $e){
		return array();
	}

	if(!count($blocks)) return array();

	$block = null;
	foreach($blocks as $obj){
		if($obj->getClass() == "ScriptModuleBlockComponent"){
			$block = $obj;
		}
	}

	if(is_null($block)) return array();

	//ラベルIDを取得とデータベースから記事の取得件数指定
	$labelId = null;
	$count = null;
	if(preg_match('/(<[^>]*[^\/]block:id=\"' . $block->getSoyId() . '\"[^>]*>)/', $template, $tmp)){
		if(preg_match('/cms:label=\"(.*?)\"/', $tmp[1], $ltmp)){
			if(isset($ltmp[1]) && is_numeric($ltmp[1])) $labelId = (int)$ltmp[1];
		}
		if(preg_match('/cms:count=\"(.*?)\"/', $tmp[1], $ctmp)){
			if(isset($ctmp[1]) && is_numeric($ctmp[1])) $count = (int)$ctmp[1];
		}
	}else{
		return array();
	}

	$entryDao = SOY2DAOFactory::create("cms.EntryDAO");
	$sql = "SELECT ent.* FROM Entry ent ".
		 "JOIN EntryLabel lab ".
		 "ON ent.id = lab.entry_id ".
		 "WHERE ent.openPeriodStart < " . time() . " ".
		 "AND ent.openPeriodEnd >= " .time() . " ".
		 "AND ent.isPublished = " . Entry::ENTRY_ACTIVE . " ";
	$binds = array();
	
	//ラベルIDを指定する場合
	if(isset($labelId)){
		$sql .= "AND lab.label_id = :labelId ";
		$binds[":labelId"] = $labelId;
	}

	$sql .= "GROUP BY ent.id ";

	if(SOY2DAOConfig::type() == "mysql"){
		$sql .= "ORDER BY Rand() ";
	}else{
		$sql .= "ORDER BY Random() ";
	}

	if(isset($count) && $count > 0) {
		$sql .= "Limit " . $count;
	}
		
	try{
		$res = $entryDao->executeQuery($sql, $binds);
	}catch(Exception $e){
		$res = array();
	}

	if(!count($res)) return array();

	$entries = array();
	foreach($res as $v){
		$entries[] = $entryDao->getObject($v);
	}

	return $entries;
}
?>

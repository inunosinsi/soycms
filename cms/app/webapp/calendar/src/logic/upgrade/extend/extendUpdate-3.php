<?php
if(SOYCMS_DB_TYPE == "sqlite"){	//sqliteの時のみ実行
	$dao = SOY2DAOFactory::create("SOYCalendar_ItemDAO");
	try{
		$res = $dao->executeQuery("SELECT id, title FROM soycalendar_item WHERE title_id IS NULL");
	}catch(Exception $e){
		$res = array();
	}
	if(count($res)){
		$same = array();	//array(title => array(id...))にしておく
		foreach($res as $v){
			$titleId = (int)$v["title"];
			if(!isset($same[$titleId])) $same[$titleId] = array();
			$same[$titleId][] = (int)$v["id"];
		}
		
		foreach($same as $titleId => $ids){
			$dao->executeUpdateQuery("UPDATE soycalendar_item SET title_id = " . $titleId . " WHERE id IN (" .implode(",", $ids) . ")");
		}
	}
	unset($dao);
}

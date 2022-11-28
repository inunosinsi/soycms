<?php
function soycalendar_update_4_execute(){
    $dao = SOY2DAOFactory::create("SOYCalendar_ItemDAO");
    try{
        $res = $dao->executeQuery("SELECT id, schedule, schedule_date FROM soycalendar_item WHERE schedule IS NOT NULL AND schedule_date IS NULL");
	}catch(Exception $e){
        $res = array();
    }
    if(!count($res)) return;
    foreach($res as $v){
		$t = soycalendar_get_timestamp_by_ymd($v["schedule"]);
		$dao->executeUpdateQuery("UPDATE soycalendar_item SET schedule = NULL, schedule_date = " . $t . " WHERE id = " . (int)$v["id"]);
    }
}

function soycalendar_update_4_execute_confirm(){
	// MySQL版のみ実行
	if(SOYCMS_DB_TYPE == "sqlite") return;
	
	$dao = SOY2DAOFactory::create("SOYCalendar_ItemDAO");
	try{
        $res = $dao->executeQuery("SELECT id, schedule FROM soycalendar_item WHERE schedule IS NOT NULL");
	}catch(Exception $e){
        $res = array();
	}
	
	if(!count($res)){
		try{
			$dao->executeUpdateQuery("ALTER TABLE soycalendar_item DROP COLUMN schedule");
		}catch(Exception $e){
			//
		}
	}
}

soycalendar_update_4_execute();
soycalendar_update_4_execute_confirm();
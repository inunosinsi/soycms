<?php

SOY2::import("util.SOYShopPluginUtil");
if(SOYShopPluginUtil::checkIsActive("reserve_calendar")){
	$dao = new SOY2DAO();

	if(SOYSHOP_DB_TYPE == "mysql"){
		$sql = "CREATE TABLE soyshop_reserve_calendar_schedule_search(
			schedule_id INTEGER NOT NULL,
			schedule_date INTEGER NOT NULL,
			UNIQUE(schedule_id, schedule_date)
		)ENGINE=InnoDB;";
	}else{
		$sql = "CREATE TABLE soyshop_reserve_calendar_schedule_search(
			schedule_id INTEGER NOT NULL,
			schedule_date INTEGER NOT NULL,
			UNIQUE(schedule_id, schedule_date)
		);";
	}

	try{
		$dao->executeQuery($sql);
	}catch(Exception $e){
		//var_dump($e);
	}
}

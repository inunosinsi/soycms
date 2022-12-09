<?php

SOY2::import("util.SOYShopPluginUtil");
if(SOYShopPluginUtil::checkIsActive("reserve_calendar")){
	$dao = new SOY2DAO();

	if(SOYSHOP_DB_TYPE == "mysql"){
		$sql = "CREATE TABLE soyshop_reserve_calendar_schedule_price(
			schedule_id INTEGER NOT NULL,
			label VARCHAR(128) NOT NULL,
			field_id VARCHAR(255) NOT NULL,
			price INTEGER NOT NULL DEFAULT 0,
			UNIQUE(schedule_id, field_id, price)
		)ENGINE=InnoDB;";
	}else{
		$sql = "CREATE TABLE soyshop_reserve_calendar_schedule_price(
			schedule_id INTEGER NOT NULL,
			label VARCHAR(128) NOT NULL,
			field_id VARCHAR NOT NULL,
			price INTEGER NOT NULL DEFAULT 0,
			UNIQUE(schedule_id, field_id, price)
		);";
	}

	try{
		$dao->executeQuery($sql);
	}catch(Exception $e){
		//var_dump($e);
	}
}

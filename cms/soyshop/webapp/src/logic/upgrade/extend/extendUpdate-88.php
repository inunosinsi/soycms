<?php
$dao = new SOY2DAO();
try{
	$res = $dao->executeQuery("SELECT * FROM soyshop_tag_cloud_dictionary LIMIT 1;");
	$dao->executeUpdateQuery("ALTER TABLE soyshop_tag_cloud_dictionary ADD COLUMN category_id INTEGER NOT NULL DEFAULT 0");

	if(SOYSHOP_DB_TYPE == "mysql"){
		$dao->executeUpdateQuery("CREATE TABLE soyshop_tag_cloud_category(id INTEGER PRIMARY KEY AUTO_INCREMENT,label VARCHAR(128) UNIQUE) ENGINE=InnoDB;");
	}else{
		$dao->executeUpdateQuery("CREATE TABLE soyshop_tag_cloud_category(id INTEGER PRIMARY KEY AUTOINCREMENT,label VARCHAR UNIQUE) ENGINE=InnoDB;");
	}
}catch(Exception $e){
	//何もしない
}

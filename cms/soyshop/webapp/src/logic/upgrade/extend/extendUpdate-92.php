<?php
$dao = new SOY2DAO();
try{
	$_res = $dao->executeQuery("SELECT id FROM soyshop_tag_cloud_dictionary LIMIT 1;");
	if(SOY2DAOConfig::type() == "mysql"){
		$sql = "CREATE TABLE soyshop_tag_cloud_category_language(
			category_id INTEGER NOT NULL DEFAULT 0,
			lang CHAR(2),
			label VARCHAR(128),
			UNIQUE(category_id, lang)
		) ENGINE=InnoDB;";
	}else{
		$sql = "CREATE TABLE soyshop_tag_cloud_category_language(
			category_id INTEGER NOT NULL DEFAULT 0,
			lang VARCHAR,
			label VARCHAR,
			UNIQUE(category_id, lang)
		);";
	}
	$dao->executeUpdateQuery($sql);
}catch(Exception $e){
	var_dump($e);
}
unset($dao);
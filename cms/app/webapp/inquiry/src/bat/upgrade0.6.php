<?php
/*
 * version 0.5.x -> 0.6.0
 */
$dao = new SOY2DAO();


try{
	//sqlite
	if(SOYCMS_DB_TYPE == "sqlite"){
		$sql = "CREATE TABLE soyinquiry_comment (
	id INTEGER primary key,
	inquiry_id INTEGER not null,
	title VARCHAR,
	author VARCHAR,
	content VARCHAR,
	create_date INTEGER
)";
	
	//mysql
	}else{
		$sql = "CREATE TABLE soyinquiry_comment (
	id INTEGER primary key AUTO_INCREMENT,
	inquiry_id INTEGER not null,
	title VARCHAR(512),
	author VARCHAR(512),
	content TEXT,
	create_date INTEGER
) TYPE = InnoDB;";
	}
	
	$dao->executeUpdateQuery($sql,array());
	
	
}catch(Exception $e){
	
}


try{
	$dao->executeUpdateQuery("alter table soyinquiry_column add column_id VARCHAR(512)",array());	
}catch(Exception $e){
	
}

try{
	$dao->executeUpdateQuery("alter table soyinquiry_inquiry add tracking_number VARCHAR(512)",array());	
}catch(Exception $e){
	
}


try{
	$dao->executeUpdateQuery("CREATE INDEX soyinquiry_tracking_number_idx on soyinquiry_inquiry(tracking_number)",array());	
}catch(Exception $e){
	
}


?>

<h1>SOY Inquiry バージョンアッププログラム(0.5 -> 0.6)</h1>

<ul>
	<li>soyinquiry_commentを追加しました。</li>
	<li>soyinquiry_column#column_idを追加しました。</li>
	<li>tracking_number#tracking_numberを追加しました。</li>
</ul>

<a href="<?php echo SOY2PageController::createLink("inquiry"); ?>">戻る</a>
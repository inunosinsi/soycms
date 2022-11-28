<?php
/*
 * version 0.6.x -> 0.7.0
 */
$dao = new SOY2DAO();


try{
	//sqlite
	if(SOYCMS_DB_TYPE == "sqlite"){
		$sql = "CREATE TABLE soy_mail_log(
	id INTEGER primary key,
	log_time integer not null,
	content VARCHAR,
	more VARCHAR
)";

	//mysql
	}else{
		$sql = "CREATE TABLE soy_mail_log(
	id INTEGER primary key AUTO_INCREMENT,
	log_time integer not null,
	content TEXT,
	more TEXT
) TYPE = InnoDB;";
	}

	$dao->executeUpdateQuery($sql,array());


}catch(Exception $e){

}


?>

<h1>SOY Mail バージョンアッププログラム(0.6 -> 0.7)</h1>

<ul>
	<li>soymail_logを追加しました。</li>
</ul>

<a href="<?php echo SOY2PageController::createLink("mail"); ?>">戻る</a>
<?php
/*
 * version 0.9.x -> 1.0.0
 */
$userDao = SOY2DAOFactory::create("SOYMailUserDAO");


try{
	//sqlite
	if(SOYCMS_DB_TYPE == "sqlite"){
		$sql = "ALTER TABLE soymail_user ADD COLUMN is_error INTEGER DEFAULT 0;";

	//mysql
	}else{
		$sql = "ALTER TABLE soymail_user ADD COLUMN is_error INTEGER DEFAULT 0 AFTER not_send;";
	}

	$userDao->executeUpdateQuery($sql,array());


}catch(Exception $e){

}

try{
	//sqlite
	if(SOYCMS_DB_TYPE == "sqlite"){
		$sql = trim(getSQLiteAdd());
	
	//mysql
	}else{
		$sql = trim(getMySQLAdd());
	}
	
	$userDao->executeUpdateQuery($sql,array());
	
}catch(Exception $e){
	
}

function getSQLiteAdd(){
	$sql = <<<SQL
CREATE TABLE soymail_reservation(
	id INTEGER primary key,
	mail_id INTEGER NOT NULL,
	is_send INTEGER NOT NULL DEFAULT 0,
	offset INTEGER NOT NULL DEFAULT 0,
	reserve_date INTEGER NOT NULL,
	schedule_date INTEGER,
	send_date INTEGER
);

SQL;

	return $sql;
}

function getMySQLAdd(){
		$sql = <<<SQL
CREATE TABLE soymail_reservation(
	id INTEGER primary key AUTO_INCREMENT,
	mail_id INTEGER NOT NULL,
	is_send INTEGER NOT NULL DEFAULT 0,
	offset INTEGER NOT NULL DEFAULT 0,
	reserve_date INTEGER NOT NULL,
	schedule_date INTEGER,
	send_date INTEGER
)ENGINE = InnoDB;

SQL;

	return $sql;
}


?>

<h1>SOY Mail バージョンアッププログラム(0.9 -> 1.0)</h1>

<ul>
	<li>ユーザテーブルに送信エラーフラグを追加</li>
	<li>cron送信用テーブルの追加</li>
</ul>

<a href="<?php echo SOY2PageController::createLink("mail"); ?>">戻る</a>
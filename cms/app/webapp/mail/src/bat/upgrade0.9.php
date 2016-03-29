<?php
/*
 * version 0.8.x -> 0.9.0
 */
$userDao = SOY2DAOFactory::create("SOYMailUserDAO");


try{
	//sqlite
	if(SOYCMS_DB_TYPE == "sqlite"){
		$sql = "ALTER TABLE soymail_user ADD COLUMN not_send INTEGER DEFAULT 0;";

	//mysql
	}else{
		$sql = "ALTER TABLE soymail_user ADD COLUMN not_send INTEGER DEFAULT 0 AFTER mail_error_count;";
		$sql .= "ALTER TABLE soymail_user MODIFY COLUMN mail_address VARCHAR(255) UNIQUE;";
	}

	$userDao->executeUpdateQuery($sql,array());


}catch(Exception $e){

}

try{
	//sqlite
	if(SOYCMS_DB_TYPE == "sqlite"){
		$sql = "CREATE TABLE soymail_soyshop_connector (config TEXT);";
	//mysql
	}else{
		$sql = "CREATE TABLE soymail_soyshop_connector (config TEXT)ENGINE=InnoDB;";
	}
	$userDao->executeUpdateQuery($sql,array());
}catch(Exception $e){
	
}

try{
	$users = $userDao->get();
}catch(Exception $e){
	$users = array();
}

if(count($users) > 0){
	foreach($users as $user){
		if($user->getIsDisabled()==1){
			$user->setNotSend(1);
		}
		$user->setIsDisabled(0);
		try{
			$userDao->update($user);
		}catch(Exception $e){
			var_dump($e);
			continue;
		}
	}
}

?>

<h1>SOY Mail バージョンアッププログラム(0.8 -> 0.9)</h1>

<ul>
	<li>SOY Shop連携のテーブルを追加しました。</li>
	<li>soymail_userテーブルに送信フラグカラムを追加しました。</li>
</ul>

<a href="<?php echo SOY2PageController::createLink("mail"); ?>">戻る</a>
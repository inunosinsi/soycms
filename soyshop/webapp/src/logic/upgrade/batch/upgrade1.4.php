<?php
/**
 * Upgrade file to SOY Shop 1.4
 * @change add new table for mypage auto login.
 * @change update data_sets for mypage reminder mail.
 *
 */
function execute(){

	//mypage auto login

	$db = new SOY2DAO();
	$exist = "select * from soyshop_auto_login;";

	try{
		$res = $db->executeQuery($exist);

	}catch(Exception $e){
		/* not exist auto login tabel */
		if(defined("SOYSHOP_SITE_DSN")&&preg_match('/mysql/',SOYSHOP_SITE_DSN)){

			$sql = sqlMySQL();
		}else{
			$sql = sqlSQLite();
		}

		$sql = explode(";",$sql);



		foreach($sql as $query){
			$query = trim($query);
			if(!$query)continue;

			try{
				$db->executeUpdateQuery($query);
			}catch(Exception $e){

			}

		}
	}

	//reminder mail
	$mail = array(
		"title" => "[#SHOP_NAME#]パスワード再設定",
    		"header" => file_get_contents(SOY2::RootDir() . "logic/init/mail/mypage/remind/header.txt"),
    		"footer" => file_get_contents(SOY2::RootDir() . "logic/init/mail/mypage/remind/footer.txt")
	);

	$title = SOYShop_DataSets::get("mail.mypage.remind.title", null);
	$header = SOYShop_DataSets::get("mail.mypage.remind.header", null);
	$footer = SOYShop_DataSets::get("mail.mypage.remind.footer", null);

	if(is_null($title)){
		SOYShop_DataSets::put("mail.mypage.remind.title",$mail["title"]);
	}

	if(is_null($header)){
		SOYShop_DataSets::put("mail.mypage.remind.header",$mail["header"]);
	}

	if(is_null($footer)){
		SOYShop_DataSets::put("mail.mypage.remind.footer",$mail["footer"]);
	}




}


function sqlSQLite(){
	$sql = <<<SQL

create table soyshop_auto_login(
	id integer primary key,
	user_id integer not null,
	session_token varchar not null,
	time_limit integer
);

SQL;

	return $sql;
}

function sqlMySQL(){
	$sql = <<<SQL

create table soyshop_auto_login(
	id integer primary key auto_increment,
	user_id integer not null,
	session_token CHAR(32) NOT NULL,
	time_limit integer
) ENGINE=InnoDB default character set utf8;

SQL;

	return $sql;
}
?>
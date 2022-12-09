<?php

set_time_limit(0);
	
//コピーしたいファイルのパスを取得する
if(!defined("SOYSHOP_TEMPLATE_ID")) define("SOYSHOP_TEMPLATE_ID", "bryon");
$cssPath = SOY2::RootDir() . "logic/init/theme/" . SOYSHOP_TEMPLATE_ID . "/common/css/";
$jsPath = SOY2::RootDir() . "logic/init/theme/" . SOYSHOP_TEMPLATE_ID . "/common/js/";

//MyPageのCSSの更新
$to = SOYSHOP_SITE_DIRECTORY . "themes/common/css/";
$res = copy($cssPath . "mypage.css", $to . "mypage.css");

//zip2address.jsのコピー
$to = SOYSHOP_SITE_DIRECTORY . "themes/common/js/";
$res = copy($jsPath . "zip2address.js", $to . "zip2address.js");

if(defined("SOYSHOP_SITE_DSN")&&preg_match('/mysql/',SOYSHOP_SITE_DSN)){
	$sql = sqlMySQL();
}else{
	$sql = sqlSQLite();
}

$dao = new SOY2DAO();
try{
	$dao->executeUpdateQuery($sql,array());
}catch(Exception $e){
	
}

//ユーザタイプの本登録への修正
SOY2::import("domain.user.SOYShop_User");
$updateSql = "UPDATE soyshop_user SET user_type = " . SOYShop_User::USERTYPE_REGISTER . " WHERE user_type IS NULL;";
try{
	$dao->executeUpdateQuery($updateSql,array());
}catch(Exception $e){
	
}

function sqlMySQL(){
	$sql = <<<SQL
ALTER TABLE soyshop_point_history ADD COLUMN point INTEGER SIGNED NOT NULL DEFAULT 0 AFTER order_id;
SQL;

	return $sql;
}

function sqlSQLite(){
	$sql = <<<SQL
ALTER TABLE soyshop_point_history ADD COLUMN point INTEGER SIGNED NOT NULL DEFAULT 0;
SQL;

	return $sql;
}
?>
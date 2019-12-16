<?php

$dao = new SOY2DAO();

#クーポンプラグイン分
try{
	$res = $dao->executeQuery("SELECT id FROM soyshop_coupon");
	$sqls = file_get_contents(SOY2::RootDir() . "module/plugins/discount_free_coupon/sql/init_" . SOYSHOP_DB_TYPE.".sql");
	preg_match('/create table soyshop_coupon_category\([\s\S]*?\);/i', $sqls, $tmp);
	if(isset($tmp[0])){
		$sql = trim($tmp[0]);
		if(SOYSHOP_DB_TYPE == "mysql"){
			$sql = str_replace(");", ")ENGINE=InnoDB;", $sql);
		}
		$dao->executeQuery($sql);
	}
}catch(Exception $e){

}

#伝票番号プラグイン分
SOY2::import("util.SOYShopPluginUtil");
if(SOYShopPluginUtil::checkIsActive("slip_number") || SOYShopPluginUtil::checkIsActive("returns_slip_number")){
	$sql = file_get_contents(SOY2::RootDir() . "module/plugins/common_order_customfield/sql/init_" . SOYSHOP_DB_TYPE . ".sql");
	try{
		$dao->executeQuery($sql);
	}catch(Exception $e){

	}
}

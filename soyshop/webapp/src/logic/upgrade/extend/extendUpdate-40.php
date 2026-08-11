<?php
$dao = new SOY2DAO();
$logic = SOY2Logic::createInstance("logic.plugin.SOYShopPluginLogic");

//在庫確認中の注文があるか？調べる
try{
	$res = $dao->executeQuery("SELECT id * FROM soyshop_order WHERE order_status = 6");
}catch(Exception $e){
	$res = array();
}

if(count($res)){
	$logic->installModule("add_status_check_stock_in");
	try{
		$dao->executeUpdatQuery("UPDATE soyshop_order SET order_status = 11 WHERE order_status = 6");

	}catch(Exception $e){
		//
	}
}

//返品済みがあるか？調べる
try{
	$res = $dao->executeQuery("SELECT id * FROM soyshop_order WHERE order_status = 7");
}catch(Exception $e){
	$res = array();
}

if(count($res)){
	$logic->installModule("add_status_check_stock_in");
	try{
		$dao->executeUpdatQuery("UPDATE soyshop_order SET order_status = 12 WHERE order_status = 7");

	}catch(Exception $e){
		//
	}
}

<?php
//@ToDo アプリケーションIDを決めてください。
define('APPLICATION_ID', "stepmail");

//SOYShop_Userオブジェクトをインクルードする
if(!defined("SOYSHOP_WEBAPP")) define("SOYSHOP_WEBAPP", dirname(dirname(dirname(dirname(__FILE__)))) . "/soyshop/webapp/");

//使用するSOY Shopのクラス
SOY2::RootDir(SOYSHOP_WEBAPP . "src/");
SOY2::imports("domain.user.*");
SOY2::import("logic.plugin.SOYShopPlugin");
SOY2::imports("domain.plugin.*");

//SOY2の設定
if(!defined("STEPMAIL_SRC")) define("STEPMAIL_SRC", dirname(__FILE__) . "/src/");
SOY2::RootDir(STEPMAIL_SRC);

//SOY2HTMLの設定
SOY2HTMLConfig::PageDir(dirname(__FILE__) . "/pages/");

//SOY2DAOの設定
SOY2DAOConfig::DaoDir(SOY2::RootDir() . "domain/");
SOY2DAOConfig::EntityDir(SOY2::RootDir() . "domain/");



//データベースの読み込み:soyshopのデータベースを使う
if(file_exists(dirname(RESERVE_SRC) . "/shop_id.php")){
	include_once(dirname(RESERVE_SRC) . "/shop_id.php");	
}else{
	define(STEPMAIL_SHOP_ID, "shop");
}
include_once(SOYSHOP_WEBAPP . "conf/shop/" . STEPMAIL_SHOP_ID . ".conf.php");
SOY2DAOConfig::Dsn(SOYSHOP_SITE_DSN);
SOY2DAOConfig::user(SOYSHOP_SITE_USER);
SOY2DAOConfig::pass(SOYSHOP_SITE_PASS);

include_once(SOY2::RootDir() . "base/common.php");


//PHP
mb_internal_encoding("UTF-8");

/**
 * 必要な定数がありましたら、ここで設定しましょう
 */

?>
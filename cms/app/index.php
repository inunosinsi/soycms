<?php
define("CMS_APPLICATION_ROOT_DIR", dirname(__FILE__) . "/");
define("CMS_COMMON", dirname(dirname(__FILE__)) . "/common/");

include_once(dirname(__FILE__)."/webapp/base/config.php");

try{
	//アプリケーションの実行
	CMSApplication::run();
	
	//表示
	CMSApplication::display();

}catch(Exception $e){
	$exception = $e;
	include_once(CMS_COMMON . "error/admin.php");
		
}
?>
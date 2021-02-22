<?php

function init_soyshop($siteId = null, $option = array(), $siteName=null, $redirect=true, $isOnlyAdmin=false){
if(!soy2_check_token()) return false;

if(strlen($siteId) > 0){
	$oldDsn = SOY2DAOConfig::Dsn();
	//document rootの末尾は/で終わるのを期待
	if(function_exists("soy2_realpath")){
		$_SERVER["DOCUMENT_ROOT"] = soy2_realpath($_SERVER["DOCUMENT_ROOT"]);
	}

	if(!defined("SOYCMS_TARGET_DIRECTORY")){
		define("SOYCMS_TARGET_DIRECTORY", $_SERVER["DOCUMENT_ROOT"]);
	}

	//target dir
	$target = soy2_realpath(SOYCMS_TARGET_DIRECTORY);
	if(!$target) return false;//dir not exist


	if(!defined("SOYCMS_TARGET_URL")){
		define("SOYCMS_TARGET_URL", SOY2PageController::createRelativeLink("/", true));
	}
	$url = SOYCMS_TARGET_URL;


	if($url[strlen($url)-1] != "/") $url .= "/";
	$url .= $siteId . "/";

	//config.phpの作成
	$dir = dirname(__FILE__) . "/webapp/conf/shop/";
	$name = $siteId . ".conf.php";

	$config = array();
	$config[] = "<?php";
	//$config[] ='include(dirname(__FILE__) . "/' . $siteId .  '.admin.conf.php");';
	$config[] = 'if(!defined("SOYSHOP_ID")) define("SOYSHOP_ID", "' . $siteId . '");';
	$config[] = 'if(!defined("SOYSHOP_SITE_DIRECTORY")) define("SOYSHOP_SITE_DIRECTORY", "' . $target. $siteId . '/");';
	$config[] = 'if(!defined("SOYSHOP_SITE_URL")) define("SOYSHOP_SITE_URL", "' . $url . '");';
	if($option["dbtype"] == "mysql"){
		$config[] = '/* configure for mysql */';
		$config[] = 'if(!defined("SOYSHOP_SITE_DSN")) define("SOYSHOP_SITE_DSN", "' . $option["dsn"] . '");';
		$config[] = 'if(!defined("SOYSHOP_SITE_USER")) define("SOYSHOP_SITE_USER", "' . $option["user"] . '");';
		$config[] = 'if(!defined("SOYSHOP_SITE_PASS")) define("SOYSHOP_SITE_PASS", "' . $option["pass"] . '");';
	}else{
		$config[] = '/* configure for sqlite */';
		$config[] = 'if(!defined("SOYSHOP_SITE_DSN")) define("SOYSHOP_SITE_DSN", "sqlite:' . $target . $siteId . '/.db/sqlite.db");';
		$config[] = 'if(!defined("SOYSHOP_SITE_USER")) define("SOYSHOP_SITE_USER","");';
		$config[] = 'if(!defined("SOYSHOP_SITE_PASS")) define("SOYSHOP_SITE_PASS","");';
	}
	$config[] = '?>';

	file_put_contents($dir . $name, implode("\n", $config));

	//admin.config.phpの作成 →　廃止
	// $name = $siteId . ".admin.conf.php";
	// $config = array();
	// $config[] = "<?php";
	// $config[] = 'define("' . $siteId . '_SOYSHOP_ID","' . $siteId . '");';
	// $config[] = 'define("' . $siteId . '_SOYSHOP_SITE_DIRECTORY","'. $target. "${siteId}/" .'");';
	// $config[] = 'define("' . $siteId . '_SOYSHOP_SITE_URL","'.$url.'");';
	// if($option["dbtype"] == "mysql"){
	// 	$config[] = '/* configure for mysql */';
	// 	$config[] = 'define("' . $siteId . '_SOYSHOP_SITE_DSN","' . $option["dsn"] . '");';
	// 	$config[] = 'define("' . $siteId . '_SOYSHOP_SITE_USER","' . $option["user"] . '");';
	// 	$config[] = 'define("' . $siteId . '_SOYSHOP_SITE_PASS","' . $option["pass"] . '");';
	// }else{
	// 	$config[] = '/* configure for sqlite */';
	// 	$config[] = 'define("' . $siteId . '_SOYSHOP_SITE_DSN","sqlite:' . $target . "${siteId}/.db/sqlite.db" . '");';
	// 	$config[] = 'define("' . $siteId . '_SOYSHOP_SITE_USER","");';
	// 	$config[] = 'define("' . $siteId . '_SOYSHOP_SITE_PASS","");';
	// }
	/** $config[] = '?>' **/;
	//
	// file_put_contents($dir . $name, implode("\n", $config));
}
$_SERVER["SCRIPT_FILENAME"] = __FILE__;

include( dirname(__FILE__) .  "/webapp/conf/admin.conf.php");
$initLogic = SOY2Logic::createInstance("logic.init.InitLogic");
$initLogic->setOption($option);

set_time_limit(0);
error_reporting(E_ALL ^ E_WARNING);
ini_set("display_errors", "On");

$log = SOYSHOP_SITE_DIRECTORY . "init.log";
file_put_contents($log, "");

$start = microtime(true);
$_start = $start;
ob_start();

echo "init start: " . date(DATE_RFC2822);
echo "\n======================================================\n";

echo "init directory\n";
$initLogic->initDirectory($isOnlyAdmin);


echo "\n------------------------------------------------------\n";

echo "init db\n";
$res = $initLogic->initDB();
echo "\n" . ( ($res) ? "success" : "failed");
echo "\n";
$end = microtime(true);
echo ($end - $start) . " ....sec";
$start = $end;
echo "\n";
file_put_contents($log,ob_get_contents(),FILE_APPEND);
ob_end_clean();ob_start();

echo "\n------------------------------------------------------\n";

$initPageLogic = SOY2Logic::createInstance("logic.init.InitPageLogic");
echo "init page";
$res = $initPageLogic->initPage($isOnlyAdmin);
echo "\n" . ( ($res) ? "success" : "failed");
echo "\n";
$end = microtime(true);
echo ($end - $start) . " ....sec";
$start = $end;
echo "\n";
file_put_contents($log,ob_get_contents(),FILE_APPEND);
ob_end_clean();ob_start();

echo "\n------------------------------------------------------\n";

echo "init category";
$res = $initPageLogic->initCategory($isOnlyAdmin);
echo "\n" . ( ($res) ? "success" : "failed");
echo "\n";
$end = microtime(true);
echo ($end - $start) . " ....sec";
$start = $end;
echo "\n";
file_put_contents($log,ob_get_contents(),FILE_APPEND);
ob_end_clean();ob_start();

echo "\n------------------------------------------------------\n";

echo "init item";
$res = $initPageLogic->initItems($isOnlyAdmin);
echo "\n" . ( ($res) ? "success" : "failed");
echo "\n";
$end = microtime(true);
echo ($end - $start) . " ....sec";
$start = $end;
echo "\n";
file_put_contents($log,ob_get_contents(),FILE_APPEND);
ob_end_clean();ob_start();

echo "\n------------------------------------------------------\n";

echo "init mail";
$res = $initPageLogic->initDefaultMail($isOnlyAdmin);
echo "\n" . ( ($res) ? "success" : "failed");
echo "\n";
$end = microtime(true);
echo ($end - $start) . " ....sec";
$start = $end;
echo "\n";
file_put_contents($log,ob_get_contents(),FILE_APPEND);
ob_end_clean();ob_start();

echo "\n------------------------------------------------------\n";

echo "init modules";

$res = $initLogic->initModules($isOnlyAdmin);
echo "\n" . ( ($res) ? "success" : "failed");
echo "\n";
$end = microtime(true);
echo ($end - $start) . " ....sec";
$start = $end;
echo "\n";

echo "\n------------------------------------------------------\n";

echo "init cart";

$res = $initPageLogic->initCart($isOnlyAdmin);
echo "\n" . ( ($res) ? "success" : "failed");
echo "\n";
$end = microtime(true);
echo ($end - $start) . " ....sec";
$start = $end;
echo "\n";

echo "\n------------------------------------------------------\n";

echo "init mypage";

$res = $initPageLogic->initMypage($isOnlyAdmin);
echo "\n" . ( ($res) ? "success" : "failed");
echo "\n";
$end = microtime(true);
echo ($end - $start) . " ....sec";
$start = $end;
echo "\n";

echo "\n------------------------------------------------------\n";
echo "init shop config";
echo "\n";
try{
	SOY2::import("domain.config.SOYShop_ShopConfig");
	$shopConfig = SOYShop_ShopConfig::load();
	$shopConfig->setAdminUrl(SOY2PageController::createRelativeLink("index.php", true));
	$shopConfig->setShopName($siteName);
	SOYShop_ShopConfig::save($shopConfig);
}catch(Exception $e){
	echo "....failed\n";
	file_put_contents($log,ob_get_contents(),FILE_APPEND);
	ob_end_clean();
	return false;
}
$end = microtime(true);
echo ($end - $start) . " ....sec";
$start = $end;
echo "\n";
file_put_contents($log,ob_get_contents(),FILE_APPEND);
ob_end_clean();ob_start();

echo "\n------------------------------------------------------\n";
echo "regist site";

$res = $initLogic->registSite($siteId,$option,$siteName);

echo "\n" . ( ($res) ? "success" : "failed");
echo "\n";
$end = microtime(true);
echo ($end - $start) . " ....sec";
$start = $end;
echo "\n";
file_put_contents($log,ob_get_contents(),FILE_APPEND);
ob_end_clean();ob_start();

echo "\n------------------------------------------------------\n";
echo "regist version";

$res = $initLogic->initDefaultVersion();

echo "\n" . ( ($res) ? "success" : "failed");
echo "\n";
$end = microtime(true);
echo ($end - $start) . " ....sec";
$start = $end;
echo "\n";
file_put_contents($log,ob_get_contents(),FILE_APPEND);
ob_end_clean();ob_start();



echo "\n======================================================\n";
echo "DONE ".date(DATE_RFC2822)."\n";
$end = microtime(true);
echo ($end - $_start) . " ....sec";
$start = $end;
echo "\n";
echo "\n";


file_put_contents($log,ob_get_contents(),FILE_APPEND);
ob_end_clean();

//ログを .db 以下に移す
@file_put_contents(SOYSHOP_SITE_DIRECTORY . ".db/init.log", file_get_contents($log), FILE_APPEND);
unlink($log);

SOY2DAOConfig::Dsn($oldDsn);
if(!$redirect)return true;
?>
<html>
<head>
<META HTTP-EQUIV="Refresh" CONTENT="5; URL=<?php echo SOY2PageController::createRelativeLink("index.php/shop"); ?>">
</head>
<body>

initialize finished ... (total:<?php echo microtime(true) - $_start; ?> sec)<br />

refreshing to <a href="<?php echo SOY2PageController::createRelativeLink("index.php/shop"); ?>">controll panel</a> after few seconds...

</body>
</html>
<?php
return true;
}/* init_soyshop_end */

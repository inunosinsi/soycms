<?php

set_time_limit(0);
	
//コピーしたいファイルのパスを取得する
if(!defined("SOYSHOP_TEMPLATE_ID")) define("SOYSHOP_TEMPLATE_ID", "bryon");
$jsPath = SOY2::RootDir() . "logic/init/theme/" . SOYSHOP_TEMPLATE_ID . "/common/js/";

//zip2address.jsのコピー
$to = SOYSHOP_SITE_DIRECTORY . "themes/common/js/";
$res = copy($jsPath . "zip2address.js", $to . "zip2address.js");
?>
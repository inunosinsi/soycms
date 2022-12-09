<?php

set_time_limit(0);
	
//コピーしたいファイルのパスを取得する
if(!defined("SOYSHOP_TEMPLATE_ID"))define("SOYSHOP_TEMPLATE_ID","bryon");
$path = SOY2::RootDir() . "logic/init/theme/".SOYSHOP_TEMPLATE_ID."/common/";

//コピー先のパスを取得する
$to = SOYSHOP_SITE_DIRECTORY . "themes/common/";

//jQueryのバージョンアップ
$res = copy($path."js/jquery.min.js",$to."js/jquery.min.js");

//jQueryMobileのインストール
copy($path."js/jquery.mobile.min.js",$to."js/jquery.mobile.min.js");

//_assetsのコピー
@mkdir($to."_assets/");
$this->copyDirectory($path."_assets/",$to."_assets/");

//jQueryMobileのCSSファイルのコピー
$res = copy($path."css/jquery.mobile.min.css",$to."css/jquery.mobile.min.css");
@mkdir($to."css/images/");
$this->copyDirectory($path."css/images/",$to."css/images/");


//CartID:smartのインストール
$templateToPath = SOYSHOP_SITE_DIRECTORY . ".template/cart/";
$cartPath = SOY2::RootDir() . "logic/init/template/".SOYSHOP_TEMPLATE_ID."/cart/";
file_put_contents($templateToPath."smart.html",$this->replaceTemplate(file_get_contents($cartPath . "smart.html")));
$res = copy($cartPath."smart.ini",$templateToPath."smart.ini");

?>
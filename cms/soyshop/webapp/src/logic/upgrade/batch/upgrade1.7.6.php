<?php
function execute(){	
	set_time_limit(0);
	
	//コピーしたいファイルのパスを取得する
	if(!defined("SOYSHOP_TEMPLATE_ID"))define("SOYSHOP_TEMPLATE_ID","bryon");
	$path = SOY2::RootDir() . "logic/init/theme/".SOYSHOP_TEMPLATE_ID."/common/";
	
	//コピー先のパスを取得する
	$to = SOYSHOP_SITE_DIRECTORY . "themes/common/";
	
	//jQueryのバージョンアップ
	$res = copy($path."js/jquery.min.js",$to."js/jquery.min.js");
	
	if($res === true){
		_echo("・jQueryのバージョンアップを行いました。(1.6.1→1.8.1)");
	}
	
	//jQueryMobileのインストール
	copy($path."js/jquery.mobile.min.js",$to."js/jquery.mobile.min.js");

	//_assetsのコピー
	@mkdir($to."_assets/");
	copyDirectory($path."_assets/",$to."_assets/");
	
	//jQueryMobileのCSSファイルのコピー
	$res = copy($path."css/jquery.mobile.min.css",$to."css/jquery.mobile.min.css");
	@mkdir($to."css/images/");
	copyDirectory($path."css/images/",$to."css/images/");
	
	if($res === true){
		_echo("・jQuery Mobileのインストールを行いました。");
	}
	
	//CartID:smartのインストール
	$templateToPath = SOYSHOP_SITE_DIRECTORY . ".template/cart/";
	$cartPath = SOY2::RootDir() . "logic/init/template/".SOYSHOP_TEMPLATE_ID."/cart/";
	file_put_contents($templateToPath."smart.html",replaceTemplate(file_get_contents($cartPath . "smart.html")));
	$res = copy($cartPath."smart.ini",$templateToPath."smart.ini");
	
	if($res === true){
		_echo("カートID:smartのHTMLをスマホ仕様に変更しました");
	}

	_echo();
	_echo();
	$link = SOY2PageController::createLink("");
	_echo("アップグレードバッチは終了しました。");
	_echo("<a href='$link'>SOY Shop管理画面に戻る</a>");
	exit;
}

function copyDirectory($from,$to){
		
	$files = scandir($from);
	
	if($from[strlen($from)-1] != "/")$from .= "/";
	if($to[strlen($to)-1] != "/")$to .= "/";
	
	foreach($files as $file){
		if($file[0] == ".")continue;
	
		if(is_dir($from . $file)){
			if(!file_exists($to.$file))mkdir($to.$file);
			copyDirectory($from . $file, $to . $file);
			continue;
		}else{
	
			file_put_contents(
				$to . $file
				,file_get_contents($from . $file)
			);
		}
	}
}

function replaceTemplate($html){
	if(!defined("SOYSHOP_SITE_NAME"))define("SOYSHOP_SITE_NAME","インテリアショップLBD");
	$url = parse_url(SOYSHOP_SITE_URL);
	$path = $url["path"];
	if($path[strlen($path)-1] == "/")$path = substr($path,0,strlen($path) - 1);
	$html = str_replace("@@SOYSHOP_URI@@",$path,$html);
	$html = str_replace("@@SOYSHOP_NAME@@",SOYSHOP_SITE_NAME,$html);

	return $html;
}

function _echo($str=""){
	echo $str."<br />";
}

function _echob($str=""){
	_echo("<b>" . $str."</b>");
}
?>
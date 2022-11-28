<?php

//テンプレートの複製
$tmpDir = SOYSHOP_SITE_DIRECTORY . ".template/";
$cartDir = $tmpDir . "cart/";
$mypageDir = $tmpDir . "mypage/";
if(is_file($cartDir . "main.html")){
	//カート
	file_put_contents($cartDir . "omame.html",file_get_contents($cartDir . "main.html"));
	file_put_contents($cartDir . "omame.ini",str_replace("main","omame",file_get_contents($cartDir . "main.ini")));

	if(SOYShop_DataSets::get("config.cart.cart_id","main")=="main"){
		SOYShop_DataSets::put("config.cart.cart_id","omame");
	}

	//マイページ
	file_put_contents($mypageDir . "omame.html",file_get_contents($mypageDir . "main.html"));
	file_put_contents($mypageDir . "omame.ini",str_replace("main","omame",file_get_contents($mypageDir . "main.ini")));

	if(SOYShop_DataSets::get("config.mypage.id","main")=="main"){
		SOYShop_DataSets::put("config.mypage.id","omame");
	}

}else{
	$omameTmpDir = SOYSHOP_WEBAPP . "src/logic/init/template/omame/";

	//カート
	file_put_contents($cartDir . "omame.html",str_replace("@@SOYSHOP_URI@@","/".SOYSHOP_ID,file_get_contents($omameTmpDir . "cart/omame.html")));
	file_put_contents($cartDir . "omame.ini",file_get_contents($omameTmpDir . "cart/omame.ini"));

	//マイページ
	file_put_contents($mypageDir . "omame.html",str_replace("@@SOYSHOP_URI@@","/".SOYSHOP_ID,file_get_contents($omameTmpDir . "mypage/omame.html")));
	file_put_contents($mypageDir . "omame.ini",file_get_contents($omameTmpDir . "mypage/omame.ini"));
}

$path = SOYSHOP_WEBAPP . "src/logic/init/theme/omame/";
$to = SOYSHOP_SITE_DIRECTORY . "themes/";
$this->copyDirectory($path,$to);
?>
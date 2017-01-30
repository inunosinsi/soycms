<?php

//reminder mail
$mail = array(
	"title" => "[#SHOP_NAME#]パスワード再設定",
		"header" => file_get_contents(SOY2::RootDir() . "logic/init/mail/mypage/remind/header.txt"),
		"footer" => file_get_contents(SOY2::RootDir() . "logic/init/mail/mypage/remind/footer.txt")
);

$title = SOYShop_DataSets::get("mail.mypage.remind.title", null);
$header = SOYShop_DataSets::get("mail.mypage.remind.header", null);
$footer = SOYShop_DataSets::get("mail.mypage.remind.footer", null);

if(is_null($title)){
	SOYShop_DataSets::put("mail.mypage.remind.title",$mail["title"]);
}

if(is_null($header)){
	SOYShop_DataSets::put("mail.mypage.remind.header",$mail["header"]);
}

if(is_null($footer)){
	SOYShop_DataSets::put("mail.mypage.remind.footer",$mail["footer"]);
}

?>
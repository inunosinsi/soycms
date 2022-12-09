<?php
//bootstrapテンプレートをコピーする
set_time_limit(0);

//コピーしたいファイルのパスを取得する
$from = SOY2::RootDir() . "logic/init/template/bryon/";

//コピー先のパスを取得する
$to = SOYSHOP_SITE_DIRECTORY . ".template/";

//bootstrapのテンプレートのコピー
foreach(array("cart", "mypage") as $t){
	foreach(array("html", "ini") as $ex){
		$html = file_get_contents($from . $t . "/bootstrap." . $ex);
		if($ex == "html"){
			$html = str_replace("@@SOYSHOP_URI@@" , "/" . SOYSHOP_ID, $html);
		}
		file_put_contents($to . $t . "/bootstrap." . $ex, $html);
	}
}

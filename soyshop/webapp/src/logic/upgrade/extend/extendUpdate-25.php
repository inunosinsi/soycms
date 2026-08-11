<?php

//各ページのクラスファイルを取得する
if(is_dir(SOYSHOP_SITE_DIRECTORY . ".page")){
	$files = scandir(SOYSHOP_SITE_DIRECTORY . ".page");
	foreach($files as $file){
		if(strpos($file, "_page.php")){
			$filename = str_replace(".php", "", $file);
			$c = file_get_contents(SOYSHOP_SITE_DIRECTORY . ".page/" . $file);
			
			//コンストラクタ名を修正
			$c = str_replace("function " . $filename . "(", "function __construct(", $c);
			$c = str_replace("WebPage::WebPage();", "parent::__construct();", $c);
			
			file_put_contents(SOYSHOP_SITE_DIRECTORY . ".page/" . $file, $c);
		}
	}
}

?>
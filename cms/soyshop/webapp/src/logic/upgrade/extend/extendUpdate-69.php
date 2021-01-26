<?php

$cnfDir = SOYSHOP_WEBAPP . "conf/shop/";
$files = soy2_scandir($cnfDir);
if(count($files)){
	foreach($files as $file){
		if(is_numeric(strpos($file, "admin.conf.php"))) continue;
		$lines = explode("\n", file_get_contents($cnfDir . $file));

		$codes = array();
		foreach($lines as $line){
			if(is_numeric(strpos($line, "define(")) && is_bool(strpos($line, "!defined("))){
				preg_match('/define\(\"(.*?)\"/', $line, $tmp);
				if(!isset($tmp[1])) contine;

				$line = "if(!defined(\"" . htmlspecialchars($tmp[1], ENT_QUOTES, "UTF-8") . "\")) " . $line;
			}
			$codes[] = $line;
		}

		file_put_contents($cnfDir . $file, implode("\n", $codes));
	}
}

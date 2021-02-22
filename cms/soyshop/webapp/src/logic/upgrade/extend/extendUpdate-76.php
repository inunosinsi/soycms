<?php
$cnfDir = SOYSHOP_WEBAPP . "conf/shop/";
$files = soy2_scandir($cnfDir);
if(count($files)){
	for(;;){
		$doExe = false;
		foreach($files as $file){
			if(is_bool(strpos($file, ".admin."))) continue;
			$doExe = true;
			$shopId = substr($file, 0, strpos($file, "."));
			$lines = explode("\n", file_get_contents($cnfDir . $file));

			$codes = array();
			foreach($lines as $line){
				if(is_bool(strpos($line, "SOYSHOP_"))){
					$codes[] = $line;
				}else{
					$line = str_replace($shopId . "_", "", $line);
					if(is_numeric(strpos($line, "SOYSHOP_ID"))){
						$codes[] = "if(!defined(\"SOYSHOP_ID\")) " . $line;
					}else if(is_numeric(strpos($line, "SOYSHOP_SITE_DIRECTORY"))){
						$codes[] = "if(!defined(\"SOYSHOP_SITE_DIRECTORY\")) " . $line;
					}else if(is_numeric(strpos($line, "SOYSHOP_SITE_URL"))){
						$codes[] = "if(!defined(\"SOYSHOP_SITE_URL\")) " . $line;
					}else if(is_numeric(strpos($line, "SOYSHOP_SITE_DSN"))){
						$codes[] = "if(!defined(\"SOYSHOP_SITE_DSN\")) " . $line;
					}else if(is_numeric(strpos($line, "SOYSHOP_SITE_USER"))){
						$codes[] = "if(!defined(\"SOYSHOP_SITE_USER\")) " . $line;
					}else if(is_numeric(strpos($line, "SOYSHOP_SITE_PASS"))){
						$codes[] = "if(!defined(\"SOYSHOP_SITE_PASS\")) " . $line;
					}
				}
			}

			if(is_numeric(file_put_contents($cnfDir . $shopId . ".conf.php", implode("\n", $codes)))){
				unlink($cnfDir . $file);
			}
		}
		if(!$doExe) break;
	}
}

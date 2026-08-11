<?php

if(!file_exists(SOYSHOP_SITE_DIRECTORY . "im.php")){
  $imFilePath = str_replace("soyshop/webapp/", "", SOYSHOP_WEBAPP) . "common/im.inc.php";
  $script = "<?php\n\$site_root = dirname(__FILE__);\ninclude_once(\"" . $imFilePath . "\");";
  file_put_contents(SOYSHOP_SITE_DIRECTORY . "im.php", $script);
}

<?php
$adminPageDir = dirname(dirname(dirname(dirname(__DIR__))))."/admin/webapp/pages/";
// /pages/Administrator/以下と/pages/Application/を廃止
foreach(array("Administrator", "Application") as $d){
	$targetPageDir = $adminPageDir.$d."/";
	if(!is_dir($targetPageDir)) continue;
	if ($dh = opendir($targetPageDir)) {
		while (($f = readdir($dh)) !== false) {
			$res = strpos($f, ".");
			if((is_numeric($res) && $res === 0)) continue;
			$targetPageFile = $targetPageDir.$f;
			if(!is_file($targetPageFile)) continue;
			//chmod($targetPageFile, 0777);
			unlink($targetPageFile);
		}
		closedir($dh);
	}
}
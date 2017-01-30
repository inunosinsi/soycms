<?php

function soyshop_pager_navigation2($html,$page){
	$obj = $page->create("soyshop_pager_list_navigation2","HTMLTemplatePage", array(
		"arguments" => array("soyshop_pager_list_navigation2",$html)
	));
	
	if(!function_exists("soyshop_pager_navigation")){
		include(dirname(__FILE__) . "/pager_navigation.php");
	}
	soyshop_pager_navigation($html,$page);

}
?>

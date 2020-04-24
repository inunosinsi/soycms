<?php
function soyshop_fixed_form_module($html,$page){

	$obj = $page->create("fixed_form_module", "HTMLTemplatePage", array(
		"arguments" => array("fixed_form_module", $html)
	));

	//商品詳細ページのみ動作
	if($page->getPageObject()->getType() == SOYShop_Page::TYPE_DETAIL){
		SOY2::import("module.plugins.fixed_form_module.util.FixedFormModuleUtil");

		//商品毎にsoyValueを切り替える
		$soyValue = FixedFormModuleUtil::getAttr($page->getItem()->getId())->getValue();
		if(strlen($soyValue)){
			$array = explode(".", $soyValue);
			if(count($array) > 1){
				unset($array[0]);
			}
			$func = "soyshop_" . implode("_", $array);

			$modulePath = SOYSHOP_SITE_DIRECTORY . ".module/" . str_replace(".", "/", $soyValue) . ".php";

			if(!function_exists($func)){
				if(file_exists($modulePath)){
					include_once($modulePath);
				}else{
					SOY2::import("module.site." . $soyValue, ".php");
				}
			}

			if(function_exists($func)){
				$func($html, $page);
			}else{
				echo "function not found : " . $func;
			}
		}
	}
}

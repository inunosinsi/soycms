<?php
SOY2::import("util.SOYShopPluginUtil");
function soyshop_custom_search($html, $htmlObj){
	$obj = $htmlObj->create("soyshop_custom_search", "HTMLTemplatePage", array(
		"arguments" => array("soyshop_custom_search", $html)
	));
	
	if(SOYShopPluginUtil::checkIsActive("build_custom_search")){
		
		$pluginDir = SOYSHOP_WEBAPP . "src/module/plugins/build_custom_search/";
		include_once($pluginDir . "common/common.php");
		$logic = new CustomSearchCommon();
		
		$default = $logic->defaultOption();
		
		//ディフォルトの項目
		foreach($default as $index => $type){
			
			$html = "";
			
			switch($type){
				case "range":
					$html = $logic->buildRangeForm($index);
					break;
				case "text":
				default:
					$q = ($index == "item_name") ? "q" : $index;
					$html = $logic->buildTextForm($q);
					break;
			}
			
			$obj->addLabel("search_" . $index, array(
				"html" => $html,
				"soy2prefix" => SOYSHOP_SITE_PREFIX
			));
		}
		
		$list = $logic->getConfig();
		
		//カスタムフィールドの項目
		foreach($list as $key => $config){
			$html = "";
			
			switch($config["type"]){
				case "range":
					$html = $logic->buildRangeForm($key);
					break;
				case "select":
					$html = $logic->buildSelectBox($key, $config["value"]);
					break;
				case "checkbox":
					$html = $logic->buildCheckBox($key, $config["value"]);
					break;
				case "radio":
					$html = $logic->buildRadioButton($key, $config["value"]);
					break;
				case "text":
				default:
					$html = $logic->buildTextForm($key);
					break;
			}
			
			$obj->addLabel("search_" . $key, array(
				"soy2prefix" => SOYSHOP_SITE_PREFIX,
				"html" => $html
			));
		}
		
		$obj->addLabel("condition_radio", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"html" => $logic->getConditionRadioForm()
		));
	}	
	
	$obj->display();
}
?>
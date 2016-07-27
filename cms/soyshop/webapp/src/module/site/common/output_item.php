<?php

function soyshop_custom_search_field($html, $htmlObj){
	$obj = $htmlObj->create("soyshop_custom_search_field", "HTMLTemplatePage", array(
		"arguments" => array("soyshop_custom_search_field", $html)
	));
	
	SOY2::import("util.SOYShopPluginUtil");
	if(SOYShopPluginUtil::checkIsActive("custom_search_field")){
		
		SOY2::import("module.plugins.custom_search_field.util.CustomSearchFieldUtil");
		
		//GETの値を変数に入れておく。そのうちページャ対応を行わなければならないため
		$params = (isset($_GET["c_search"])) ? $_GET["c_search"] : array();
		
		//商品名
		$obj->addInput("custom_search_item_name", array(
			"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
			"type" => "text",
			"name" => "c_search[item_name]",
			"value" => (isset($params["item_name"]) && strlen($params["item_name"])) ? $params["item_name"] : null
		));
		
		//商品名
		$obj->addInput("custom_search_item_code", array(
			"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
			"type" => "text",
			"name" => "c_search[item_code]",
			"value" => (isset($params["item_code"]) && strlen($params["item_code"])) ? $params["item_code"] : null
		));
		
		//商品価格
		foreach(array("min", "max") as $t){
			$obj->addInput("custom_search_item_price_" . $t, array(
				"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
				"type" => "text",
				"name" => "c_search[item_price_" . $t . "]",
				"value" => (isset($params["item_price_" . $t]) && strlen($params["item_price_" . $t])) ? $params["item_price_" . $t] : null
			));
		}
		
		//カテゴリのセレクトボックス
		$obj->addSelect("custom_search_item_category", array(
			"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
			"name" => "c_search[item_category]",
			"options" => CustomSearchFieldUtil::getIsOpenCategoryList(),
			"selected" => (isset($params["item_category"])) ? (int)$params["item_category"] : false 
		));
		
		foreach(CustomSearchFieldUtil::getConfig() as $key => $field){
			switch($field["type"]){
				case CustomSearchFieldUtil::TYPE_RANGE:
					foreach(array("start", "end") as $t){
						$obj->addInput("custom_search_" . $key . "_" . $t, array(
							"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
							"type" => "number",
							"name" => "c_search[" . $key . "_" . $t . "]",
							"value" => (isset($params[$key . "_" . $t]) && strlen($params[$key . "_" . $t])) ? (int)$params[$key . "_" . $t] : null
						));
					}
					break;
				case CustomSearchFieldUtil::TYPE_CHECKBOX:
					if(strlen($field["option"])){
						foreach(explode("\n", $field["option"]) as $i => $o){
							$o = trim($o);	//改行を除く
							if(!strlen($o)) continue;
							$obj->addCheckBox("custom_search_" . $key . "_" . $i, array(
								"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
								"type" => "checkbox",
								"name" => "c_search[" . $key . "][]",
								"value" => $o,
								"selected" => (isset($params[$key]) && is_array($params[$key]) && in_array($o, $params[$key])),
								"label" => $o,
								"elementId" => "custom_search_" . $key . "_" . $i
							));
						}
					}
					break;
				case CustomSearchFieldUtil::TYPE_RADIO:
					if(strlen($field["option"])){
						foreach(explode("\n", $field["option"]) as $i => $o){
							$o = trim($o);	//改行を除く
							if(!strlen($o)) continue;
							$obj->addCheckBox("custom_search_" . $key . "_" . $i, array(
								"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
								"type" => "radio",
								"name" => "c_search[" . $key . "]",
								"value" => $o,
								"selected" => ((!isset($params[$key]) && $i === 0) || (isset($params[$key]) && $o === $params[$key])),
								"label" => $o,
								"elementId" => "custom_search_" . $key . "_" . $i
							));
						}
					}
					break;
				case CustomSearchFieldUtil::TYPE_SELECT:
					$options = array();
					foreach(explode("\n", $field["option"]) as $o){
						$options[trim($o)] = trim($o);
					}
					$obj->addSelect("custom_search_" . $key, array(
						"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
						"name" => "c_search[" . $key . "]",
						"options" => $options,
						"selected" => (isset($params[$key])) ? $params[$key] : false
					));
					break;
				default:
					$obj->addInput("custom_search_" . $key, array(
						"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
						"name" => "c_search[" . $key . "]",
						"value" => (isset($params[$key])) ? $params[$key] : null
					));
			}
				
		}
	}	
	
	$obj->display();
}
?>
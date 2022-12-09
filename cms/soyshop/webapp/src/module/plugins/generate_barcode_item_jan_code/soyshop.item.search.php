<?php

class GenerateBarcodeItemJanCodeItemSearch extends SOYShopItemSearch{

	function setParameter($params){
		$v = (isset($params) && is_string($params)) ? $params : "";
		if(strlen($v)){
			SOY2::import("module.plugins.generate_barcode_item_jan_code.util.GenerateJancodeUtil");
			$q = "id IN (SELECT item_id FROM soyshop_item_attribute WHERE item_field_id = '" . GenerateJancodeUtil::FIELD_ID . "' AND item_value LIKE :jancode)";
			$binds[":jancode"] = "%" . $v . "%";
			return array("queries" => array($q), "binds" => $binds);
		}
	}

	function searchItems($params){
		$v = (isset($params) && is_string($params)) ? $params : "";
		$form = "<input type=\"text\" class=\"form-control\" name=\"SearchForm[customs][generate_barcode_item_jan_code]\" value=\"" . $v . "\">";
		return array("label" => "JANコード", "form" => $form);
	}
}
SOYShopPlugin::extension("soyshop.item.search", "generate_barcode_item_jan_code", "GenerateBarcodeItemJanCodeItemSearch");

<?php
SOY2::import("module.plugins.fixed_form_module.util.FixedFormModuleUtil");
class FixedFormModuleCustomField extends SOYShopItemCustomFieldBase{

	function doPost(SOYShop_Item $item){
		if(isset($_POST[FixedFormModuleUtil::PLUGIN_ID])){
			FixedFormModuleUtil::save($item->getId(), $_POST[FixedFormModuleUtil::PLUGIN_ID]);
		}
	}

	function getForm(SOYShop_Item $item){
		$list = FixedFormModuleUtil::getAllModuleList();
		$selected = FixedFormModuleUtil::getAttr($item->getId())->getValue();

		$cnf = FixedFormModuleUtil::getConfig();
		$label = (isset($cnf["form_name"]) && strlen($cnf["form_name"])) ? $cnf["form_name"] : "shop:module=\"fixed_form_module\"内で実行するモジュールの選択";

		$html = array();
		$html[] = "<div class=\"form-group\">";
		$html[] = "	<label>" . $label . "</label><br>";
		$html[] = "	<select name=\"" . FixedFormModuleUtil::PLUGIN_ID . "\">";
		$html[] = "		<option></option>";
		foreach($list as $moduleId => $name){
			if(strlen($selected) && $moduleId == $selected){
				$html[] = "<option value=\"" . $moduleId . "\" selected=\"selected\">" . $name . "</option>";
			}else{
				$html[] = "<option value=\"" . $moduleId . "\">" . $name . "</option>";
			}
		}
		$html[] = "	</select>";
		//$html[] = "  <a href=\"" . SOY2PageController::createLink("Site.Template#html_module_list") . "\" class=\"btn btn-default\">モジュールの作成</a><br>";
		// $html[] = "<table style=\"margin-top:5px;\">";
		// $html[] = "<tr>";
		// $html[] = "<th>モジュールについて</th>";
		// $html[] = "<td>";
		// $html[] = "<a href=\"https://saitodev.co/soycms/soyshop/tutorial/80\" target=\"_blank\">共通箇所はHTMLモジュールで管理する - saitodev.co</a><br>";
		// $html[] = "<a href=\"https://saitodev.co/soycms/soyshop/tutorial/96\" target=\"_blank\">共通箇所をPHPモジュールで管理する - saitodev.co</a>";
		// $html[] = "</td>";
		// $html[] = "</tr>";
		// $html[] = "</table>";
		$html[] = "</div>";
		return implode("\n", $html);
	}

	/**
	 * onOutput
	 */
	function onOutput($htmlObj, SOYShop_Item $item){}
	function onDelete($id){}
}

SOYShopPlugin::extension("soyshop.item.customfield", "fixed_form_module", "FixedFormModuleCustomField");

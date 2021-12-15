<?php
/*
 */
include(dirname(__FILE__) . "/common/common.php");
class CommonAdditionOptionCustomField extends SOYShopItemCustomFieldBase{

	function doPost(SOYShop_Item $item){
		foreach(array("flag", "price", "name", "text") as $typ){
			$fieldId = "addition_option_" . $typ;
			$attr = soyshop_get_item_attribute_object($item->getId(), $fieldId);
			switch($typ){
				case "flag":
					$v = (isset($_POST[$fieldId])) ? 1 : null;
					break;
				case "price":
					$v = soyshop_convert_number($_POST[$fieldId], 0);
					break;
				default:
					$v = (isset($_POST[$fieldId]) && is_string($_POST[$fieldId])) ? trim($_POST[$fieldId]) : null;
			}
			$attr->setValue($v);
			soyshop_save_item_attribute_object($attr);
		}
	}

	function getForm(SOYShop_Item $item){
		$itemId = (is_numeric($item->getId())) ? (int)$item->getId() : 0;
		list($flag, $price, $name, $text) = self::_get($itemId);

		$style = "style=\"text-align:right;ime-mode:inactive;\"";

		$html = array();

		$html[] = "<br>";
		$html[] = "<div class=\"alert alert-info\">加算オプションの設定</div>";

		$html[] = "<div class=\"form-group\">";
		$html[] = "<label>公開側の表示設定</label><br>";
		$html[] = "<label>";
		$html[] = "<input type=\"checkbox\" name=\"addition_option_flag\" value=\"1\" ";
		if($flag){
			$html[] = "checked=\"checked\"";
		}
		$html[] = " />";
		$html[] = "公開側に加算オプションを表示する</label>";
		$html[] = "</div>";

		$html[] = "<div class=\"form-group\">";
		$html[] = "	<label>加算項目(カートに入れた時に表示されます)</label>";
		$html[] = "	<div class=\"form-inline\">";
		$html[] = "		<input type=\"text\" name=\"addition_option_name\" class=\"form-control\" value=\"" . $name."\" />";
		$html[] = "	</div>";
		$html[] = "	<br>";
		$html[] = "	<label>加算額の設定</label>";
		$html[] = "	<div class=\"form-inline\">";
		$html[] = "		<input type=\"text\" name=\"addition_option_price\" class=\"form-control\" value=\"" . $price."\" " . $style." size=\"5\" />&nbsp;円";
		$html[] = "	</div>";
		$html[] = "	<br>";
		$html[] = "	<label>加算時の文言</label>";
		$html[] = "	<div class=\"form-inline\">";
		$html[] = "		<textarea name=\"addition_option_text\" class=\"form-control\" style=\"width:100%;\">".htmlspecialchars($text, ENT_QUOTES, "UTF-8")."</textarea>";
		$html[] = "		<div class=\"alert alert-warning\">※##PRICE##は公開側で加算額で設定した値に置換されます</div>";
		$html[] = "	</div>";
		$html[] = "</div>";
		$html[] = "<div class=\"alert alert-info\">加算オプションの設定ここまで</div>";

		return implode("\n", $html);
	}

	function onOutput($htmlObj, SOYShop_Item $item){
		$itemId = (is_numeric($item->getId())) ? (int)$item->getId() : 0;
		list($flag, $price, $name, $text) = self::_get($itemId);

		if(strlen($text)) $text = str_replace("##PRICE##", $price, $text);

		$html = array();

		if($flag){
			//valueには商品IDを入れておく
			$html[] = "<input type=\"hidden\" name=\"item_option[addition_option]\" value=\"0\" />";
			$html[] = "<input type=\"checkbox\" name=\"item_option[addition_option]\" value=\"" . $itemId . "\" id=\"addition_option\">";
			$html[] = "<label for=\"addition_option\">" . nl2br(htmlspecialchars($text, ENT_QUOTES, "UTF-8")) . "</label>";
		}

		$htmlObj->addModel("addition_option_visible", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"visible" => $flag
		));

		$htmlObj->addLabel("addition_option", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"html" => implode("\n", $html)
		));
	}

	private function _get(int $itemId){
		$values = array();
		$isFirst = true;
		//はじめての登録だということがわかることはすべての値がnullであるということ
		foreach(array("flag", "price", "name", "text") as $typ){
			$fieldId = "addition_option_" . $typ;
			$values[$typ] = ($itemId > 0) ? soyshop_get_item_attribute_value($itemId, $fieldId) : null;
			if(!is_null($values[$typ])) $isFirst = false;
		}

		//値を整形する
		$cnf = ($isFirst) ? CommonAdditionCommon::getConfig() : array();
		if(count($cnf)) $cnf["flag"] = false;
		foreach(array("flag", "price", "name", "text") as $typ){
			switch($typ){
				case "flag":
					if(isset($cnf[$typ])){
						$values[$typ] = false;
					}else{
						$values[$typ] = (isset($values[$typ]) && $values[$typ] == 1);
					}
					break;
				case "price":
					$values[$typ] = (isset($cnf[$typ])) ? $cnf[$typ] : (int)$values[$typ];
					break;
				default:
					$values[$typ] = (isset($cnf[$typ])) ? $cnf[$typ] : (string)$values[$typ];
					break;
			}
		}

		return array($values["flag"], $values["price"], $values["name"], $values["text"]);
	}
}

SOYShopPlugin::extension("soyshop.item.customfield", "common_addition_option", "CommonAdditionOptionCustomField");

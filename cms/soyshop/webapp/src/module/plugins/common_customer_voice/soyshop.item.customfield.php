<?php
include(dirname(__FILE__) . "/common.php");
class CommonCustomerVoice extends SOYShopItemCustomFieldBase{

	
	function doPost(SOYShop_Item $item){
		$fieldId = "customer_voice_plugin";
		$attr = soyshop_get_item_attribute_object($item->getId(), $fieldId);

		$v = null;
		if(isset($_POST[$fieldId])){
			$names = $_POST["customer_voice_plugin"];
			$values = $_POST["customer_voice_text"];
			
			$arr = array();
			for($i = 0;$i < count($names); $i++){
				if(strlen($values[$i]) > 0){
					$obj = array();
					$obj["name"] = $names[$i];
					$obj["value"] = $values[$i];
					$arr[] = $obj;
				}
			}
			if(count($arr)) $v = soy2_serialize($arr);
		}
		$attr->setValue($v);
		soyshop_save_item_attribute_object($attr);
	}

	function getForm(SOYShop_Item $item){

		$class = new CustomerVoiceClass();

		$values = (is_numeric($item->getId())) ? soy2_unserialize(soyshop_get_item_attribute_value($item->getId(), "customer_voice_plugin", "string")) : array();

		$html = array();

		$html[] = "<h1>お客様の声</h1>";

		$counter = 1;
		if(count($values)){
			for($i = 0; $i < count($values); $i++){

				$html[] = "<dt>お客様の声" . $counter . "</dt>";
				$html[] = "<dd>";

				$html[] = $class->buildNameArea($values[$i]["name"]);
				$html[] = $class->buildTextArea($values[$i]["value"]);

				$html[] = "</dd>";

				$counter++;
			}
		}

		$html[] = "<dt>お客様の声" . $counter . "</dt>";
		$html[] = "<dd>";

		$html[] = $class->buildNameArea();
		$html[] = $class->buildTextArea();

		$html[] = "</dd>";

		return implode("\n", $html);
	}

	/**
	 * onOutput
	 */
	function onOutput($htmlObj, SOYShop_Item $item){

		$values = (is_numeric($item->getId())) ? soy2_unserialize(soyshop_get_item_attribute_value($item->getId(), "customer_voice_plugin", "string")) : array();

		$htmlObj->addModel("is_voice_list", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"visible" => (count($values) > 0)
		));

		$htmlObj->createAdd("voice_list", "CommonCustomerVoiceList", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"list" => $values
		));

	}

	function onDelete(int $itemId){
		SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO")->deleteByItemId($itemId);
	}
}

class CommonCustomerVoiceList extends HTMLList{

	protected function populateItem($entity) {

		$this->addLabel("name", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => (isset($entity["name"])) ? $entity["name"] : ""
		));

		$this->addLabel("voice", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"html" => (isset($entity["value"])) ? nl2br($entity["value"]) : ""
		));
	}
}

SOYShopPlugin::extension("soyshop.item.customfield","common_customer_voice","CommonCustomerVoice");

<?php
include(dirname(__FILE__) . "/common.php");
class CommonCustomerVoice extends SOYShopItemCustomFieldBase{

	private $itemAttributeDao;

	function doPost(SOYShop_Item $item){
			
		$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		$itemDAO = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		$array = $dao->getByItemId($item->getId());
		
		$configs = SOYShop_ItemAttributeConfig::load(true);
			
		$key = "customer_voice_plugin";
		$value = 1;
			
		try{
			$dao->delete($item->getId(),$key);
		}catch(Exception $e){
			
		}
			
		if(isset($_POST["customer_voice_plugin"])){
			
			$names = $_POST["customer_voice_plugin"];
			$values = $_POST["customer_voice_text"];
			
			$array = array();
			for($i = 0;$i < count($names); $i++){
				if(strlen($values[$i]) > 0){
					$obj = array();
					$obj["name"] = $names[$i];
					$obj["value"] = $values[$i];
					$array[] = $obj;
				}
			}
			
			if(count($array) > 0){
				try{
					$obj = new SOYShop_ItemAttribute();
					$obj->setItemId($item->getId());
					$obj->setFieldId($key);
					$obj->setValue(soy2_serialize($array));
	
					$dao->insert($obj);
				}catch(Exception $e){
					//
				}
			}
		}			
	}

	function getForm(SOYShop_Item $item){
		
		$class = new CustomerVoiceClass();

		$this->prepare();

		try{
			$obj = $this->itemAttributeDao->get($item->getId(), "customer_voice_plugin");
		}catch(Exception $e){
			$obj = new SOYShop_ItemAttribute();
		}

		$values = soy2_unserialize($obj->getValue());
		if(!$values) $values = array();
		
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
		
		$this->prepare();
		
		$class = new CustomerVoiceClass();
		
		try{
			$obj = $this->itemAttributeDao->get($item->getId(), "customer_voice_plugin");
		}catch(Exception $e){
			$obj = new SOYShop_ItemAttribute();
		}
		
		$values = soy2_unserialize($obj->getValue());
		if(!$values) $values = array();
		
		$htmlObj->addModel("is_voice_list", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"visible" => (count($values) > 0)
		));
		
		$htmlObj->createAdd("voice_list", "CommonCustomerVoiceList", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"list" => $values
		));

	}

	function onDelete($id){
		$attributeDAO = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		$attributeDAO->deleteByItemId($id);
	}
	
	function prepare(){
		if(!$this->itemAttributeDao) $this->itemAttributeDao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
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
?>
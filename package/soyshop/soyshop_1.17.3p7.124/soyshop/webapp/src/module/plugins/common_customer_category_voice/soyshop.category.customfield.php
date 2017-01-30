<?php
include(dirname(__FILE__) . "/common.php");
class CommonCustomerCategoryVoiceCustomfield extends SOYShopCategoryCustomFieldBase{

	function doPost($category){

		$dao = SOY2DAOFactory::create("shop.SOYShop_CategoryAttributeDAO");
		$categoryDAO = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO");
		$array = $dao->getByCategoryId($category->getId());
		
		$configs = SOYShop_CategoryAttributeConfig::load(true);
			
		$key = "customer_category_voice_plugin";
		$value = 1;
			
		try{
			$dao->delete($category->getId(),$key);
		}catch(Exception $e){
			
		}
			
		if(isset($_POST["customer_category_voice_plugin"])){
			
			$names = $_POST["customer_category_voice_plugin"];
			$values = $_POST["customer_category_voice_text"];
			
			$array = array();
			for($i = 0; $i < count($names); $i++){
				if(strlen($values[$i]) > 0){
					$obj = array();
					$obj["name"] = $names[$i];
					$obj["value"] = $values[$i];
					$array[] = $obj;
				}
				
			}
			
			if(count($array) > 0){
				try{
					$obj = new SOYShop_CategoryAttribute();
					$obj->setCategoryId($category->getId());
					$obj->setFieldId($key);
					$obj->setValue(soy2_serialize($array));
	
					$dao->insert($obj);
				}catch(Exception $e){
					//
				}
			}
		}
	}

	function getForm($category){
		
		$class = new CustomerCategoryVoiceClass();

		$dao = SOY2DAOFactory::create("shop.SOYShop_CategoryAttributeDAO");
		try{
			$obj = $dao->get($category->getId(), "customer_category_voice_plugin");
		}catch(Exception $e){
			$obj = new SOYShop_CategoryAttribute();
		}

		$values = soy2_unserialize($obj->getValue());
		if(!$values) $values = array();
		
		$html = array();
		$html[] = "<h4>お客様の声</h4>";
		
		$counter = 1;
		if(count($values)){
			for($i = 0; $i < count($values); $i++){
							
				$html[] = $class->buildNameArea($values[$i]["name"]);
				$html[] = $class->buildTextArea($values[$i]["value"]);
							
				$counter++;
			}
		}
				
		$html[] = $class->buildNameArea();
		$html[] = $class->buildTextArea();
		
		return implode("\n", $html);
	}

	function onDelete($id){
		$attributeDAO = SOY2DAOFactory::create("shop.SOYShop_CategoryAttributeDAO");
		$attributeDAO->deleteByCategoryId($id);
	}
}

SOYShopPlugin::extension("soyshop.category.customfield","common_category_customfield","CommonCustomerCategoryVoiceCustomfield");
?>
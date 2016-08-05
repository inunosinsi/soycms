<?php

class CommonOrderDateCustomfieldConfigFormPage extends WebPage{

    function __construct() {
    	SOY2DAOFactory::importEntity("SOYShop_DataSets");
    }
    
    function doPost(){
    	
    	$attributeDao = SOY2DAOFactory::create("order.SOYShop_OrderDateAttributeDAO");
    	
    	if(isset($_POST["create"])){
    		
			$configs = SOYShop_OrderDateAttributeConfig::load();
			
			$custom_id = $_POST["custom_id"];

			$config = new SOYShop_OrderDateAttributeConfig();
			$config->setLabel($_POST["custom_new_name"]);
			$config->setFieldId($custom_id);
			$config->setType($_POST["custom_type"]);

			$configs[] = $config;

			SOYShop_OrderDateAttributeConfig::save($configs);
			SOY2PageController::jump("Config.Detail?plugin=common_order_date_customfield&updated=created");
			
		}

		//update
		if(isset($_POST["update_submit"])){
			$fieldId = $_POST["update_submit"];

			$configs = SOYShop_OrderDateAttributeConfig::load(true);
			$config = $configs[$fieldId];
			SOY2::cast($config, (object)$_POST["obj"]);

			SOYShop_OrderDateAttributeConfig::save($configs);
		}

		//advanced config
		if(isset($_POST["update_advance"])){
			$fieldId = $_POST["update_advance"];

			$configs = SOYShop_OrderDateAttributeConfig::load(true);
			$config = $configs[$fieldId];
			$value = $this->checkValidate($_POST["config"]);
			$config->setConfig($value);

			SOYShop_OrderDateAttributeConfig::save($configs);
		}

		//delete
		if(isset($_POST["delete_submit"])){
			$fieldId = $_POST["delete_submit"];
			$configs = SOYShop_OrderDateAttributeConfig::load(true);
			unset($configs[$fieldId]);

			SOYShop_OrderDateAttributeConfig::save($configs);
		}

		//move
		if(isset($_POST["move_up"]) || isset($_POST["move_down"])){
			$fieldId = $_POST["field_id"];

			$configs = SOYShop_OrderDateAttributeConfig::load(true);
			$keys = array_keys($configs);
			$currentKey = array_search($fieldId,$keys);
			$swap = (isset($_POST["move_up"])) ? $currentKey - 1 :$currentKey + 1;

			if($swap >= 0 && $swap < count($keys)){
				$tmp = $keys[$currentKey];
				$keys[$currentKey] = $keys[$swap];
				$keys[$swap] = $tmp;

				$tmpArray = array();
				foreach($keys as $index => $value){
					$field = $configs[$value];
					$tmpArray[$field->getFieldId()] = $field;
				}

				SOYShop_OrderDateAttributeConfig::save($tmpArray);
			}
		}

		SOY2PageController::jump("Config.Detail?plugin=common_order_date_customfield&updated");
    }
    
    function checkValidate($value){
    	
    	$start = $value["attributeYearStart"];
    	$end = $value["attributeYearEnd"];
    	
    	if(strlen($start) > 0){
    		$start = mb_convert_kana($start, "a");
    		if(!is_numeric($start)) $start = "";
    		if(strlen($start) != 4) $start = "";
    	}
    	
    	if(strlen($end) > 0){
    		$end = mb_convert_kana($end, "a");
    		if(!is_numeric($end)) $end = "";
    		if(strlen($end) != 4) $end = "";
    	}
    	
    	if($start > $end) $end = "";
    	
    	$value["attributeYearStart"] = $start;
    	$value["attributeYearEnd"] = $end;
    	
    	return $value;
    }
    
    function execute(){
    	
    	WebPage::WebPage();
    	
    	$this->addModel("updated", array(
			"visible" => (isset($_GET["updated"]))
		));
		
		$this->addModel("error", array(
			"visible" => (isset($_GET["error"]))
		));

		$this->addForm("create_form");

		$attributeDao = SOY2DAOFactory::create("order.SOYShop_OrderDateAttributeDAO");
		$config = SOYShop_OrderDateAttributeConfig::load();

		$types = SOYShop_OrderDateAttributeConfig::getTypes();
		$this->addSelect("custom_type_select", array(
			"options" => $types,
			"name" => "custom_type"
		));

		$this->createAdd("field_list", "FieldList", array(
			"list" => $config,
			"types" => $types
		));
    }

    function setConfigObj($obj) {
		$this->config = $obj;
	}
}

class FieldList extends HTMLList{

	private $types;

	protected function populateItem($entity,$key){

		/* 情報表示用 */
		$this->addLabel("label", array(
			"text"=>$entity->getLabel(),
			"attr:id" => "label_text_" . $key,
		));

		$this->addLabel("field_text", array(
			"text"=> $entity->getFieldId(),
		));

		$this->addLabel("type", array(
			"text"=> $this->types[$entity->getType()],
			"attr:id" => "type_text_" . $key,
		));

		$this->addLabel("display_form", array(
			"text"=>'cms:id="' . $entity->getFieldId().'"'
		));

		/* 設定変更用 */
		$this->addLink("toggle_update", array(
			"link" => "javascript:void(0)",
			"onclick" => '$(\'#label_input_' . $key . '\').show();' .
						'$(\'#label_text_' . $key . '\').hide();' .
						'$(\'#type_select_' . $key . '\').show();' .
						'$(\'#type_text_' . $key . '\').hide();' .
						'$(\'#update_link_' . $key . '\').show();' .
						'$(this).hide();'
		));

		$this->addLink("update_link", array(
			"link" => "javascript:void(0)",
			"attr:id" => "update_link_" . $key,
			"onclick" => '$(\'#update_submit_' . $key . '\').click();' .
						'return false;'
		));

		$this->addInput("update_submit", array(
			"name" => "update_submit",
			"value" => $entity->getFieldId(),
			"attr:id" => "update_submit_" . $key
		));

		$this->addInput("label_input", array(
			"name" => "obj[label]",
			"attr:id" => "label_input_" . $key,
			"value" => $entity->getLabel(),
		));

		$this->addSelect("type_select", array(
			"name" => "obj[type]",
			"options" => $this->types,
			"attr:id" => "type_select_" . $key,
			"selected" => $entity->getType(),
		));

		/* 順番変更用 */
		$this->addInput("field_id", array(
			"name" => "field_id",
			"value" => $entity->getFieldId(),
		));

		/* 削除用 */
		$this->addInput("delete_submit", array(
			"name" => "delete_submit",
			"value" => $entity->getFieldId(),
			"attr:id" => "delete_submit_" . $key
		));

		$this->addLink("delete", array(
			"text"=>"削除",
			"link"=>"javascript:void(0);",
			"onclick"=>'if(confirm("delete \"' . $entity->getLabel() . '\"?")){$(\'#delete_submit_' . $key . '\').click();}return false;'
		));

		/* 高度な設定 */
		$this->addLink("toggle_config", array(
			"link" => "javascript:void(0)",
			"text" => "詳細設定",
			"onclick" => '$(\'#field_config_' . $key . '\').toggle();',
			"style" => ($entity->getAttributeName() || $entity->getAttributeDescription()) ? "background-color:yellow;" : ""
		));

		
		$this->addModel("field_config", array(
			"attr:id" => "field_config_" . $key
		));
		
		$this->addInput("attribute_name", array(
			"name" => "config[attributeName]",
			"value" => $entity->getAttributeName()
		));
		$this->addTextArea("attribute_description", array(
			"name" => "config[attributeDescription]",
			"value" => $entity->getAttributeDescription()
		));
		
		$this->addInput("attribute_year_start", array(
			"name" => "config[attributeYearStart]",
			"value" => $entity->getAttributeYearStart(),
			"style" => "text-align:right;ime-mode:inactive;",
			"size" => 5
		));
		$this->addInput("attribute_year_end", array(
			"name" => "config[attributeYearEnd]",
			"value" => $entity->getAttributeYearEnd(),
			"style" => "text-align:right;ime-mode:inactive;",
			"size" => 5
		));

		$this->addInput("update_advance", array(
			"value"=>"設定保存",
			"onclick"=>'$(\'#update_advance_submit_' . $key . '\').click();return false;'
		));

		$this->addInput("update_advance_submit", array(
			"name" => "update_advance",
			"value" => $entity->getFieldId(),
			"attr:id" => "update_advance_submit_" . $key
		));

	}

	function getTypes() {
		return $this->types;
	}
	function setTypes($types) {
		$this->types = $types;
	}
}
?>
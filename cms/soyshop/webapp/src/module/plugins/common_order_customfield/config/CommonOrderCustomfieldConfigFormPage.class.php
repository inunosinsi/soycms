<?php

class CommonOrderCustomfieldConfigFormPage extends WebPage{

    function __construct() {
    	SOY2DAOFactory::importEntity("SOYShop_DataSets");
    }

    function doPost(){

    	$dao = SOY2DAOFactory::create("order.SOYShop_OrderAttributeDAO");

    	if(isset($_POST["create"])){

			$configs = SOYShop_OrderAttributeConfig::load();
			$custom_id = $_POST["custom_id"];

			$config = new SOYShop_OrderAttributeConfig();
			$config->setLabel($_POST["custom_new_name"]);
			$config->setFieldId($custom_id);
			$config->setType($_POST["custom_type"]);

			$configs[] = $config;

			SOYShop_OrderAttributeConfig::save($configs);
			SOY2PageController::jump("Config.Detail?plugin=common_order_customfield&updated=created");

		}

		//update
		if(isset($_POST["update_submit"])){
			$fieldId = $_POST["update_submit"];

			$configs = SOYShop_OrderAttributeConfig::load(true);
			$config = $configs[$fieldId];
			SOY2::cast($config, (object)$_POST["obj"]);

			SOYShop_OrderAttributeConfig::save($configs);
		}

		//advanced config
		if(isset($_POST["update_advance"])){
			$fieldId = $_POST["update_advance"];

			$configs = SOYShop_OrderAttributeConfig::load(true);
			$config = $configs[$fieldId];
			$value = $this->checkValidate($_POST["config"]);
			$config->setConfig($value);

			SOYShop_OrderAttributeConfig::save($configs);
		}

		//delete
		if(isset($_POST["delete_submit"])){
			$fieldId = $_POST["delete_submit"];

			$configs = SOYShop_OrderAttributeConfig::load(true);
			unset($configs[$fieldId]);

			SOYShop_OrderAttributeConfig::save($configs);
		}

		//move
		if(isset($_POST["move_up"]) || isset($_POST["move_down"])){
			$fieldId = $_POST["field_id"];

			$configs = SOYShop_OrderAttributeConfig::load(true);

			$keys = array_keys($configs);
			$currentKey = array_search($fieldId, $keys);
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

				SOYShop_OrderAttributeConfig::save($tmpArray);
			}
		}

		SOY2PageController::jump("Config.Detail?plugin=common_order_customfield&updated");
    }

    function checkValidate($value){
    	$value["attributeOther"] = (isset($value["attributeOther"])) ? 1 : 0;
    	return $value;
    }

    function execute(){
    	parent::__construct();

    	$this->addModel("updated", array(
			"visible" => (isset($_GET["updated"]))
		));

		$this->addModel("error", array(
			"visible" => (isset($_GET["error"]))
		));

		$this->addForm("create_form");


		$dao = SOY2DAOFactory::create("order.SOYShop_OrderAttributeDAO");
		$config = SOYShop_OrderAttributeConfig::load();

		$types = SOYShop_OrderAttributeConfig::getTypes();
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

	protected function populateItem($entity, $key){

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
			"text"=>'cms:id="'.$entity->getFieldId().'"'
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
			"style" => ($entity->getAttributeDescription() || $entity->getDefaultValue() || $entity->getIsRequired()) ? "background-color:yellow;" : ""
		));

		$this->addModel("field_config", array(
			"attr:id" => "field_config_" . $key
		));

		//必須項目
		$this->addCheckBox("is_required", array(
			"name" => "config[isRequired]",
			"value" => SOYShop_OrderAttribute::IS_REQUIRED,
			"selected" => ($entity->getIsRequired() == SOYShop_OrderAttribute::IS_REQUIRED),
			"label" => "このカスタムフィールドを必須項目にする"
		));

		//初期値
		$this->addInput("default_value", array(
			"name" => "config[defaultValue]",
			"value" => $entity->getDefaultValue()
		));

		$this->addModel("display_description_type_checkbox", array(
			"visible" => ($entity->getType() == SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_CHECKBOX)
		));

		$this->addTextArea("attribute_description", array(
			"name" => "config[attributeDescription]",
			"value" => $entity->getAttributeDescription()
		));

		$this->addTextArea("option", array(
			"name" => "config[option]",
			"value" => $entity->getOption()
		));

		$this->addModel("with_options", array(
			"visible" => $entity->hasOption()
		));

		$this->addModel("display_option_type_checkbox", array(
			"visible" => ($entity->getType() == SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_CHECKBOX)
		));

		$this->addModel("with_radio_options", array(
			"visible" => $entity->hasRadioOption()
		));

		$this->addCheckBox("attribute_other", array(
			"name" => "config[attributeOther]",
			"value" => SOYShop_OrderAttribute::CUSTOMFIELD_ATTRIBUTE_OTHER,
			"selected" => ($entity->getAttributeOther() == SOYShop_OrderAttribute::CUSTOMFIELD_ATTRIBUTE_OTHER),
			"elementId" => "attribute_other"
		));
		$this->addInput("attribute_other_text", array(
			"name" => "config[attributeOtherText]",
			"value" => $entity->getAttributeOtherText()
		));

		$this->addModel("with_file_options", array(
			"visible" => $entity->hasFileOption()
		));

		$this->addTextArea("file_option", array(
			"name" => "config[fileOption]",
			"value" => $entity->getFileOption()
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

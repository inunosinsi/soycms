<?php
include(dirname(dirname(__FILE__)) . "/common/common.php");
class BuildCustomSearchConfigFormPage extends WebPage{

    function __construct() {
    	SOY2DAOFactory::importEntity("SOYShop_DataSets");
    }

    function doPost(){

    	if(soy2_check_token()&&isset($_POST["Config"])){
    		$config = $_POST["Config"];

    		SOYShop_DataSets::put("build_custom_search",soy2_serialize($config));
			SOY2PageController::jump("Config.Detail?plugin=build_custom_search&updated");
    	}
    }

    function execute(){
    	parent::__construct();
    	    	
    	$this->addForm("form");

		$fieldConfig = CustomSearchCommon::getFieldConfig(true);

		$this->createAdd("search_field_list", "SearchFieldList", array(
			"list" => $fieldConfig,
			"config" => CustomSearchCommon::getConfig()
		));

    }

    function setConfigObj($obj) {
		$this->config = $obj;
	}
}

class SearchFieldList extends HTMLList{

	private $config;

	protected function populateItem($entity, $key){

		$config = (isset($this->config[$key])) ? $this->config[$key] : array();

		$this->addSelect("field_type", array(
			"name" => "Config[$key][type]",
			"options" => CustomSearchCommon::typeList(),
			"selected" => (isset($config["type"])) ? $config["type"] : null
		));

		$this->addLabel("field_name", array(
			"text" => $entity->getLabel()
		));

		$this->addTextArea("field_value", array(
			"name" => "Config[$key][value]",
			"value" => (isset($config["value"])) ? $config["value"] : ""
		));

		$this->addLabel("field_id", array(
			"text" => $key
		));
	}

	function setConfig($config){
		$this->config = $config;
	}
}
?>

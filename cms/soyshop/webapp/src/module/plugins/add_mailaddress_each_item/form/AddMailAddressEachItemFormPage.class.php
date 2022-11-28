<?php

class AddMailAddressEachItemFormPage extends WebPage{

    private $configObj;
    private $itemId;

    function __construct(){
		SOY2::import("module.plugins.add_mailaddress_each_item.util.AddMailAddressEachItemUtil");
	}

    function execute(){
        parent::__construct();

        $this->addInput("email", array(
            "name" => "AddMailAddress",
            "value" => soyshop_get_item_attribute_value($this->itemId, AddMailAddressEachItemUtil::PLUGIN_ID . "_" . AddMailAddressEachItemUtil::MODE_EMAIL)
        ));
    }

    function setConfigObj($configObj){
        $this->configObj = $configObj;
    }

    function setItemId($itemId){
        $this->itemId = $itemId;
    }
}

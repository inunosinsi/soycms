<?php

class CancelMailFormPage extends WebPage{

    private $configObj;
    private $itemId;

    function __construct(){
        SOY2::import("module.plugins.common_cancel_mail.util.CancelMailUtil");
    }

    function execute(){
        parent::__construct();

        $this->addInput("email", array(
            "name" => "CancelMail",
            "value" => soyshop_get_item_attribute_value($this->itemId, CancelMailUtil::PLUGIN_ID . "_" . CancelMailUtil::MODE_EMAIL, "string")
        ));
    }

    function setConfigObj($configObj){
        $this->configObj = $configObj;
    }

    function setItemId($itemId){
        $this->itemId = $itemId;
    }
}

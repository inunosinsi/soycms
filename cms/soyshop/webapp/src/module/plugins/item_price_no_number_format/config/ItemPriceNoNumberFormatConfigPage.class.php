<?php

class ItemPriceNoNumberFormatConfigPage extends WebPage{

    private $configObj;

    function __construct(){}

    function execute(){
        if(method_exists("WebPage", "WebPage")){
            WebPage::WebPage();
        }else{
            WebPage::__construct();
        }
    }

    function setConfigObj($configObj){
        $this->configObj = $configObj;
    }
}

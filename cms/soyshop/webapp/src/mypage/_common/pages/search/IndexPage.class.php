<?php

class IndexPage extends MainMyPagePageBase{

    function __construct() {
        SOY2::import("module.plugins.user_custom_search_field.util.UserCustomSearchFieldUtil");

    	parent::__construct();

        self::buildForm();
        self::buildSearchResult();
    }

    private function buildForm(){

        $this->addForm("form", array(
            "method" => "GET"
        ));

        $html = array();

        SOY2::import("util.SOYShopPluginUtil");
        if(SOYShopPluginUtil::checkIsActive("custom_search_field")){

            SOY2::import("module.plugins.user_custom_search_field.component.FieldFormComponent");

            $html[] = "<dl>";

            foreach(UserCustomSearchFieldUtil::getConfig() as $key => $field){
                $html[] = "<dt>" . htmlspecialchars($field["label"], ENT_QUOTES, "UTF-8") . "</dt>";
                $html[] = "<dd>" . FieldFormComponent::buildForm($key, $field, null, true, false) . "</dd>";
            }

            $html[] = "</dl>";
        }

        $this->addLabel("build_form_area", array(
            "html" => implode("\n", $html)
        ));
    }

    private function buildSearchResult(){
        if(isset($_GET["u_search"])){
            $searchLogic = SOY2Logic::createInstance("module.plugins.user_custom_search_field.logic.SearchLogic");
            $searchLogic->search($this->getMyPage()->getId(), 1, 15);    //currentには1、limitには15を仮で入れておく
        }
    }
}

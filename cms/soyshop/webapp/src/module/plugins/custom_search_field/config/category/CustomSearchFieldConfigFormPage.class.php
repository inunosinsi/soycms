<?php

class CustomSearchFieldConfigFormPage extends WebPage{

    private $configObj;
    private $languages;

    function __construct(){
        SOY2::import("module.plugins.custom_search_field.util.CustomSearchFieldUtil");
        SOY2::import("module.plugins.custom_search_field.component.CustomSearchFieldListComponent");
        SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
        if(SOYShopPluginUtil::checkIsActive("util_multi_language")){
            $this->languages = UtilMultiLanguageUtil::allowLanguages();
        }else{
            $this->languages = array(UtilMultiLanguageUtil::LANGUAGE_JP => "日本語");
        }
    }

    function doPost(){

        if(soy2_check_token()){

            if($_POST["create"]){
                $key = trim($_POST["custom_key"]);

                //DBへカラムを追加する
                if(SOY2Logic::createInstance("module.plugins.custom_search_field.logic.DataBaseLogic", array("mode" => "category"))->addColumn($key, $_POST["custom_type"])){
                    $config = CustomSearchFieldUtil::getCategoryConfig();

                    $config[$key] = array(
                        "label" => trim($_POST["custom_label"]),
                        "type" => $_POST["custom_type"]
                    );

                    CustomSearchFieldUtil::saveCategoryConfig($config);
                    $this->configObj->redirect("category&updated");
                }
            }
        }

        //advanced config
        if(isset($_POST["update_advance"])){
            $key = $_POST["update_advance"];
            $config = CustomSearchFieldUtil::getCategoryConfig();
            $config[$key]["option"] = $_POST["config"]["option"];
            $config[$key]["default"] = (isset($_POST["config"]["default"])) ? $_POST["config"]["default"] : null;
            $config[$key]["sitemap"] = (isset($_POST["config"]["sitemap"])) ? $_POST["config"]["sitemap"] : null;

            CustomSearchFieldUtil::saveCategoryConfig($config);
            $this->configObj->redirect("category&updated");
        }

        //delete
        if(isset($_POST["delete_submit"])){
            $key = $_POST["delete_submit"];

            //カラムの削除を試みる:SQLiteではカラムを削除できない
            SOY2Logic::createInstance("module.plugins.custom_search_field.logic.DataBaseLogic", array("mode" => "category"))->deleteColumn($key);

            $config = CustomSearchFieldUtil::getCategoryConfig();
            unset($config[$key]);

            CustomSearchFieldUtil::saveCategoryConfig($config);
            $this->configObj->redirect("category&deleted");
        }

        //move
        if(isset($_POST["move_up"]) || isset($_POST["move_down"])){
            $fieldId = $_POST["field_id"];

            $configs = CustomSearchFieldUtil::getCategoryConfig();

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
                    $tmpArray[$value] = $field;
                }

                CustomSearchFieldUtil::saveCategoryConfig($tmpArray);
                $this->configObj->redirect("category");
            }
        }

        $this->configObj->redirect("category&error");
    }

    function execute(){
        parent::__construct();

		$this->addLabel("nav", array(
			"html" => LinkNaviAreaComponent::build()
		));

        foreach(array("error", "deleted") as $t){
            DisplayPlugin::toggle($t, isset($_GET[$t]));
        }

        $this->createAdd("field_list", "CustomSearchFieldListComponent", array(
            "list" => CustomSearchFieldUtil::getCategoryConfig(),
            "languages" => $this->languages,
            "mode" => "category"
        ));

        self::buildCreateForm();

        $this->addLabel("example_tag_list", array(
            "text" => self::buildExampleTags()
        ));
    }

    private function buildCreateForm(){
        $this->addForm("create_form");

        $this->addSelect("custom_type_select", array(
            "name" => "custom_type",
            "options" => CustomSearchFieldUtil::getTypeList()
        ));
    }

    private function buildExampleTags(){
        $html = array();

        foreach(CustomSearchFieldUtil::getCategoryConfig() as $key => $field){
            $html[] = "\t" . $field["label"] . ":\n";

            switch($field["type"]){
                case CustomSearchFieldUtil::TYPE_INTEGER:
                    $html[] = "\t<input type=\"number\" c_csf:id=\"custom_search_" . $key . "\">\n\n";
                    break;
                case CustomSearchFieldUtil::TYPE_RANGE:
                    $html[] = "\t<input type=\"number\" c_csf:id=\"custom_search_" . $key . "_start\">～";
                    $html[] = "<input type=\"number\" c_csf:id=\"custom_search_" . $key . "_end\">\n\n";
                    break;
                case CustomSearchFieldUtil::TYPE_CHECKBOX:
                    if(isset($field["option"][UtilMultiLanguageUtil::LANGUAGE_JP])) {
                        foreach(explode("\n", $field["option"][UtilMultiLanguageUtil::LANGUAGE_JP]) as $i => $o){
                            $o = trim($o);
                            $html[] = "\t<input type=\"checkbox\" c_csf:id=\"custom_search_" . $key . "_" . $i . "\">\n";
                        }
                    }
                    $html[] = "\n";
                    break;
                case CustomSearchFieldUtil::TYPE_RADIO:
                    if(isset($field["option"][UtilMultiLanguageUtil::LANGUAGE_JP])) {
                        foreach(explode("\n", $field["option"][UtilMultiLanguageUtil::LANGUAGE_JP]) as $i => $o){
                            $o = trim($o);
                            $html[] = "\t<input type=\"radio\" c_csf:id=\"custom_search_" . $key . "_" . $i . "\">\n";
                        }
                    }
                    $html[] = "\n";
                    break;
                case CustomSearchFieldUtil::TYPE_SELECT:
                    $html[] = "\t<select c_csf:id=\"custom_search_" . $key . "\"><option value=\"\"></option></select>\n\n";
                    break;
                default:
                    $html[] = "\t<input type=\"text\" c_csf:id=\"custom_search_" . $key . "\">\n\n";
            }

            if($field["type"] == CustomSearchFieldUtil::TYPE_CHECKBOX){
                $html[] = "\t" . $field["label"] . "(セレクトボックス):\n";
                $html[] = "\t<select c_csf:id=\"custom_search_" . $key . "_select\"><option value=\"\"></option></select>\n\n";
            }
        }

        return "\t" . trim(implode("", $html));
    }


    function setConfigObj($configObj){
        $this->configObj = $configObj;
    }
}

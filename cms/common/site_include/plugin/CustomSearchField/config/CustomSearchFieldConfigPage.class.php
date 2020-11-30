<?php

class CustomSearchFieldConfigPage extends WebPage {

	private $pluginObj;

	function __construct(){
		SOY2::import("site_include.plugin.CustomSearchField.util.CustomSearchFieldUtil");
		SOY2::import("site_include.plugin.CustomSearchField.component.CustomSearchFieldListComponent");
	}

	function doPost(){

        if(soy2_check_token()){

            if($_POST["create"]){
                $key = trim($_POST["custom_key"]);

                //DBへカラムを追加する
                if(SOY2Logic::createInstance("site_include.plugin.CustomSearchField.logic.DataBaseLogic")->addColumn($key, $_POST["custom_type"])){
                    $config = CustomSearchFieldUtil::getConfig();

                    $config[$key] = array(
                        "label" => trim($_POST["custom_label"]),
                        "type" => $_POST["custom_type"]
                    );

                    CustomSearchFieldUtil::saveConfig($config);
                    CMSPlugin::redirectConfigPage();
                }
            }
        }

        //advanced config
        if(isset($_POST["update_advance"])){
            $key = $_POST["update_advance"];
            $config = CustomSearchFieldUtil::getConfig();
			foreach(array("option", "other", "default", "br") as $t){
				$config[$key][$t] = (isset($_POST["config"][$t])) ? $_POST["config"][$t] : null;
			}

            CustomSearchFieldUtil::saveConfig($config);
            CMSPlugin::redirectConfigPage();
        }

        //delete
        if(isset($_POST["delete_submit"])){
            $key = $_POST["delete_submit"];

            //カラムの削除を試みる:SQLiteではカラムを削除できない
            SOY2Logic::createInstance("site_include.plugin.CustomSearchField.logic.DataBaseLogic")->deleteColumn($key);

            $config = CustomSearchFieldUtil::getConfig();
            unset($config[$key]);

            CustomSearchFieldUtil::saveConfig($config);
            CMSPlugin::redirectConfigPage();
        }

        //move
        if(isset($_POST["move_up"]) || isset($_POST["move_down"])){
            $fieldId = $_POST["field_id"];

            $configs = CustomSearchFieldUtil::getConfig();

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

                CustomSearchFieldUtil::saveConfig($tmpArray);
                CMSPlugin::redirectConfigPage();
            }
        }

        CMSPlugin::redirectConfigPage();
    }

	function execute(){
		parent::__construct();

		$this->createAdd("field_list", "CustomSearchFieldListComponent", array(
            "list" => CustomSearchFieldUtil::getConfig(),
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
		$configs = CustomSearchFieldUtil::getConfig();
		if(!count($configs)) return "";

		$html = array();
        foreach($configs as $key => $field){
            $html[] = "\t" . $field["label"] . ":\n";

            switch($field["type"]){
                case CustomSearchFieldUtil::TYPE_INTEGER:
                    $html[] = "\t<input type=\"number\" csf:id=\"custom_search_" . $key . "\">\n\n";
                    break;
                case CustomSearchFieldUtil::TYPE_RANGE:
                    $html[] = "\t<input type=\"number\" csf:id=\"custom_search_" . $key . "_start\">～";
                    $html[] = "<input type=\"number\" csf:id=\"custom_search_" . $key . "_end\">\n\n";
                    break;
                case CustomSearchFieldUtil::TYPE_CHECKBOX:
                    if(isset($field["option"])) {
                        foreach(explode("\n", $field["option"]) as $i => $o){
                            $o = trim($o);
                            $html[] = "\t<input type=\"checkbox\" csf:id=\"custom_search_" . $key . "_" . $i . "\">\n";
                        }
                    }
                    $html[] = "\n";
                    break;
                case CustomSearchFieldUtil::TYPE_RADIO:
                    if(isset($field["option"])) {
                        foreach(explode("\n", $field["option"]) as $i => $o){
                            $o = trim($o);
                            $html[] = "\t<input type=\"radio\" csf:id=\"custom_search_" . $key . "_" . $i . "\">\n";
                        }
                    }
                    $html[] = "\n";
                    break;
                case CustomSearchFieldUtil::TYPE_SELECT:
                    $html[] = "\t<select csf:id=\"custom_search_" . $key . "\"><option value=\"\"></option></select>\n\n";
                    break;
                default:
                    $html[] = "\t<input type=\"text\" csf:id=\"custom_search_" . $key . "\">\n\n";
            }

            if($field["type"] == CustomSearchFieldUtil::TYPE_CHECKBOX){
                $html[] = "\t" . $field["label"] . "(セレクトボックス):\n";
                $html[] = "\t<select csf:id=\"custom_search_" . $key . "_select\"><option value=\"\"></option></select>\n\n";
            }
        }

        return "\t" . trim(implode("", $html));
    }

	function setPluginObj($pluginObj) {
		$this->pluginObj = $pluginObj;
	}
}

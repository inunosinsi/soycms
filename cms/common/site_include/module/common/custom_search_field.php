<?php

function soycms_custom_search_field($html, $htmlObj){
    $obj = $htmlObj->create("soycms_custom_search_field", "HTMLTemplatePage", array(
        "arguments" => array("soycms_custom_search_field", $html)
    ));

	//プラグインがアクティブかどうか？
	if(file_exists(_SITE_ROOT_ . "/.plugin/CustomSearchField.active")){
		SOY2::import("site_include.plugin.CustomSearchField.util.CustomSearchFieldUtil");

        //GETの値を変数に入れておく。そのうちページャ対応を行わなければならないため
        $params = (isset($_GET["c_search"])) ? $_GET["c_search"] : array();

		//記事検索
        $obj->addInput("custom_search_entry", array(
            "soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
            "type" => "text",
            "name" => "q",
            "value" => (isset($_GET["q"]) && strlen(trim($_GET["q"]))) ? trim($_GET["q"]) : null
        ));

		$configs = CustomSearchFieldUtil::getConfig();
		$prefix = CustomSearchFieldUtil::PLUGIN_PREFIX;
		$name = "c_search";
		if(count($configs)){
			foreach($configs as $key => $field){
				$isContinue = false;
                switch($field["type"]){
                    case CustomSearchFieldUtil::TYPE_RANGE:
                        foreach(array("start", "end") as $t){
                            $obj->addInput("custom_search_" . $key . "_" . $t, array(
                                "soy2prefix" => $prefix,
                                "type" => "number",
                                "name" => $name . "[" . $key . "_" . $t . "]",
                                "value" => (isset($params[$key . "_" . $t]) && strlen($params[$key . "_" . $t])) ? (int)$params[$key . "_" . $t] : null
                            ));
                        }
                        break;
                    case CustomSearchFieldUtil::TYPE_CHECKBOX:
                        if(!isset($field["option"])) {
							$isContinue = true;
							break;
						}

                        if(strlen($field["option"])){
                            $opt = array();
                            foreach(explode("\n", $field["option"]) as $i => $o){
                                $o = trim($o);    //改行を除く
                                if(!strlen($o)) continue;
                                $opt[] = $o;
                                $obj->addCheckBox("custom_search_" . $key . "_" . $i, array(
                                    "soy2prefix" => $prefix,
                                    "type" => "checkbox",
                                    "name" => $name . "[" . $key . "][]",
                                    "value" => $o,
                                    "selected" => (isset($params[$key]) && is_array($params[$key]) && in_array($o, $params[$key])),
                                    "label" => $o,
                                    "elementId" => "custom_search_" . $key . "_" . $i
                                ));
                            }

                            /**
                             * セレクトボックスバージョン
                             */
                            $obj->addSelect("custom_search_" . $key . "_select", array(
                                "soy2prefix" => $prefix,
                                "name" => $name . "[" . $key . "][]",
                                "options" => $opt,
                                "selected" => (isset($params[$key][0])) ? $params[$key][0] : null
                            ));
                        }
                        break;
                    case CustomSearchFieldUtil::TYPE_RADIO:
                        if(!isset($field["option"])) $isContinue = true;;

                        if(strlen($field["option"])){
                            foreach(explode("\n", $field["option"]) as $i => $o){
                                $o = trim($o);    //改行を除く
                                if(!strlen($o)) continue;

                                if(isset($field["default"]) && $field["default"] == 1){
                                    $selected = ((!isset($params[$key]) && $i === 0) || (isset($params[$key]) && $o === $params[$key]));
                                }else{
                                    $selected = (isset($params[$key]) && $o === $params[$key]);
                                }

                                $obj->addCheckBox("custom_search_" . $key . "_" . $i, array(
                                    "soy2prefix" => $prefix,
                                    "type" => "radio",
                                    "name" => $name . "[" . $key . "]",
                                    "value" => $o,
                                    "selected" => $selected,
                                    "label" => $o,
                                    "elementId" => "custom_search_" . $key . "_" . $i
                                ));
                            }
                        }
                        break;
                    case CustomSearchFieldUtil::TYPE_SELECT:
                        if(!isset($field["option"])) {
							$isContinue = true;
							break;
						};

                        $options = array();
                        foreach(explode("\n", $field["option"]) as $o){
                            $options[trim($o)] = trim($o);
                        }
                        $obj->addSelect("custom_search_" . $key, array(
                            "soy2prefix" => $prefix,
                            "name" => $name . "[" . $key . "]",
                            "options" => $options,
                            "selected" => (isset($params[$key])) ? $params[$key] : false
                        ));
                        break;
                    default:
                        $obj->addInput("custom_search_" . $key, array(
                            "soy2prefix" => $prefix,
                            "name" => $name . "[" . $key . "]",
                            "value" => (isset($params[$key])) ? $params[$key] : null
                        ));
                }
				if(!$isContinue) continue;
            }
		}
    }

    $obj->display();
}

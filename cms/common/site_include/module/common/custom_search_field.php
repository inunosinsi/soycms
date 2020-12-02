<?php

function soycms_custom_search_field($html, $htmlObj){
    $obj = $htmlObj->create("soycms_custom_search_field", "HTMLTemplatePage", array(
        "arguments" => array("soycms_custom_search_field", $html)
    ));

	//プラグインがアクティブかどうか？
	if(file_exists(_SITE_ROOT_ . "/.plugin/CustomSearchField.active")){
		SOY2::import("site_include.plugin.CustomSearchField.util.CustomSearchFieldUtil");

        //GETの値を変数に入れておく。そのうちページャ対応を行わなければならないため
		$q = CustomSearchFieldUtil::getParameter("q");
        $csfParams = CustomSearchFieldUtil::getParameter("c_search");

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
                                "value" => (isset($csfParams[$key . "_" . $t]) && strlen($csfParams[$key . "_" . $t])) ? (int)$csfParams[$key . "_" . $t] : null
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
							$defaultSelectedIndex = null;	//セレクトボックス用
                            foreach(explode("\n", $field["option"]) as $i => $o){
								$defaultSelected = false;
								$o = trim($o);	//改行を除く
								if(strlen($o) && $o[0] == "*") {
									$o = substr($o, 1);	//先頭の*を除く
									$defaultSelected = true;
									$defaultSelectedIndex = $i;
								}
								if(!strlen($o)) continue;
								$opt[] = $o;
								if(CMS_CUSTOM_SEARCH_FIRST_TIME_DISPLAY){ 	// 初回の検索で項目名の頭に*が付いているものは選択済みにする
									$selected = $defaultSelected;
								}else{
									$selected = (isset($csfParams[$key]) && is_array($csfParams[$key]) && in_array($o, $csfParams[$key]));
								}

                                $obj->addCheckBox("custom_search_" . $key . "_" . $i, array(
                                    "soy2prefix" => $prefix,
                                    "type" => "checkbox",
                                    "name" => $name . "[" . $key . "][]",
                                    "value" => $o,
                                    "selected" => $selected,
                                    "label" => $o,
                                    "elementId" => "custom_search_" . $key . "_" . $i
                                ));
                            }

                            /**
                             * セレクトボックスバージョン
                             */
							if(CMS_CUSTOM_SEARCH_FIRST_TIME_DISPLAY){
								$selected = (isset($opt[$defaultSelectedIndex])) ? $opt[$defaultSelectedIndex] : null;
							}else{	// 初回の検索で項目名の頭に*が付いているものは選択済みにする
								$selected = (isset($csfParams[$key][0])) ? $csfParams[$key][0] : null;
							}

                            $obj->addSelect("custom_search_" . $key . "_select", array(
                                "soy2prefix" => $prefix,
                                "name" => $name . "[" . $key . "][]",
                                "options" => $opt,
                                "selected" => $selected
                            ));
                        }
                        break;
                    case CustomSearchFieldUtil::TYPE_RADIO:
                        if(!isset($field["option"])) $isContinue = true;;

                        if(strlen($field["option"])){
                            foreach(explode("\n", $field["option"]) as $i => $o){
								$defaultSelected = false;
								$o = trim($o);	//改行を除く
								if(strlen($o) && $o[0] == "*") {
									$o = substr($o, 1);	//先頭の*を除く
									$defaultSelected = true;
								}
								if(!strlen($o)) continue;

								if(CMS_CUSTOM_SEARCH_FIRST_TIME_DISPLAY){
									if(isset($field["default"]) && $field["default"] == 1){
										$selected = ((!isset($csfParams[$key]) && $i === 0) || (isset($csfParams[$key]) && $o === $csfParams[$key]));
									}else{
										$selected = $defaultSelected;
									}
								}else{
									$selected = (isset($csfParams[$key]) && $o === $csfParams[$key]);
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

								if(CMS_CUSTOM_SEARCH_FIRST_TIME_DISPLAY){
									$selected = $defaultSelected;
								}else{
									$selected = (isset($csfParams[$key]) && is_array($csfParams[$key]) && in_array($o, $csfParams[$key]));
								}
								$obj->addCheckBox("custom_search_" . $key . "_checkbox_" .$i, array(
									"soy2prefix" => $prefix,
									"type" => "checkbox",
									"name" => $name . "[" . $key . "][]",
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
						$defaultSelected = null;
                        foreach(explode("\n", $field["option"]) as $o){
							$o = trim($o);	//改行を除く
							if(strlen($o) && $o[0] == "*") {
								$o = substr($o, 1);	//先頭の*を除く
								$defaultSelected = $o;
							}
							$options[$o] = $o;
                        }

						if(CMS_CUSTOM_SEARCH_FIRST_TIME_DISPLAY){
							$selected = $defaultSelected;
						}else{
							$selected = (isset($csfParams[$key])) ? $csfParams[$key] : false;
						}
                        $obj->addSelect("custom_search_" . $key, array(
                            "soy2prefix" => $prefix,
                            "name" => $name . "[" . $key . "]",
                            "options" => $options,
                            "selected" => $selected
                        ));
                        break;
                    default:
                        $obj->addInput("custom_search_" . $key, array(
                            "soy2prefix" => $prefix,
                            "name" => $name . "[" . $key . "]",
                            "value" => (isset($csfParams[$key])) ? $csfParams[$key] : null
                        ));
                }
				if(!$isContinue) continue;
            }
		}

		$reqUri = $_SERVER["REQUEST_URI"];
		if(strpos($reqUri, "?") !== false){
			$reqUri = substr($reqUri, 0, strpos($reqUri, "?"));
		}

		//リセットボタン
		$obj->addLink("reset_link", array(
			"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
			"link" => $reqUri . "?reset"
		));
    }

    $obj->display();
}

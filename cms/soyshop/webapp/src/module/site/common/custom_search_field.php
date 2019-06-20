<?php

function soyshop_custom_search_field($html, $htmlObj){
    $obj = $htmlObj->create("soyshop_custom_search_field", "HTMLTemplatePage", array(
        "arguments" => array("soyshop_custom_search_field", $html)
    ));

    SOY2::import("util.SOYShopPluginUtil");
    if(SOYShopPluginUtil::checkIsActive("custom_search_field")){

        SOY2::import("module.plugins.custom_search_field.util.CustomSearchFieldUtil");

        //検索条件はセッションから取得
        $params = CustomSearchFieldUtil::getParameter("c_search");
		$catParams = CustomSearchFieldUtil::getParameter("cat_search");

        //商品名
        $obj->addInput("custom_search_item_name", array(
            "soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
            "type" => "text",
            "name" => "c_search[item_name]",
            "value" => (isset($params["item_name"]) && strlen($params["item_name"])) ? $params["item_name"] : null
        ));

        //商品コード
        $obj->addInput("custom_search_item_code", array(
            "soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
            "type" => "text",
            "name" => "c_search[item_code]",
            "value" => (isset($params["item_code"]) && strlen($params["item_code"])) ? $params["item_code"] : null
        ));

        //商品価格
        foreach(array("min", "max") as $t){
            $obj->addInput("custom_search_item_price_" . $t, array(
                "soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
                "type" => "text",
                "name" => "c_search[item_price_" . $t . "]",
                "value" => (isset($params["item_price_" . $t]) && strlen($params["item_price_" . $t])) ? $params["item_price_" . $t] : null
            ));
        }

        //カテゴリのセレクトボックス
        $obj->addSelect("custom_search_item_category", array(
            "soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
            "name" => "c_search[item_category]",
            "options" => CustomSearchFieldUtil::getIsOpenCategoryList(),
            "selected" => (isset($params["item_category"])) ? (int)$params["item_category"] : false
        ));

		$obj->addInput("custom_search_csf_free_word", array(
			"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
            "name" => "c_search[csf_free_word]",
			"value" => (isset($params["csf_free_word"])) ? $params["csf_free_word"] : ""
		));

		//隠しモード　テキストエリアでフリーワード検索
		$obj->addTextArea("custom_search_csf_free_word_textarea", array(
			"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
            "name" => "c_search[csf_free_word]",
			"value" => (isset($params["csf_free_word"])) ? $params["csf_free_word"] : ""
		));

        //カスタムサーチフィールドとカテゴリカスタムフィールドの検索用フォームを出力
        foreach(array("item", "category") as $mode){
			switch($mode){
				case "category":
					$configs = CustomSearchFieldUtil::getCategoryConfig();
					$prefix = CustomSearchFieldUtil::PLUGIN_CATEGORY_PREFIX;
					$name = "cat_search";
					$csfParams = $catParams;
					break;
				default:
					$configs = CustomSearchFieldUtil::getConfig();
					$prefix = CustomSearchFieldUtil::PLUGIN_PREFIX;
					$name = "c_search";
					$csfParams = $params;
			}

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

								$obj->addSelect("custom_search_" . $key . "_" . $t . "_select", array(
									"soy2prefix" => $prefix,
									"name" => $name . "[" . $key . "_" . $t . "]",
									"options" => range(1, 9),	//決め打ち @ToDo管理画面で指定できるようにしたい
									"selected" => (isset($csfParams[$key . "_" . $t]) && strlen($csfParams[$key . "_" . $t])) ? (int)$csfParams[$key . "_" . $t] : null
								));
	                        }
	                        break;
	                    case CustomSearchFieldUtil::TYPE_CHECKBOX:
	                        if(!isset($field["option"][SOYSHOP_PUBLISH_LANGUAGE])) {
								$isContinue = true;
								break;
							}

	                        if(strlen($field["option"][SOYSHOP_PUBLISH_LANGUAGE])){
	                            $opt = array();
	                            foreach(explode("\n", $field["option"][SOYSHOP_PUBLISH_LANGUAGE]) as $i => $o){
	                                $o = trim($o);    //改行を除く
	                                if(!strlen($o)) continue;
	                                $opt[] = $o;
	                                $obj->addCheckBox("custom_search_" . $key . "_" . $i, array(
	                                    "soy2prefix" => $prefix,
	                                    "type" => "checkbox",
	                                    "name" => $name . "[" . $key . "][]",
	                                    "value" => $o,
	                                    "selected" => (isset($csfParams[$key]) && is_array($csfParams[$key]) && in_array($o, $csfParams[$key])),
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
	                                "selected" => (isset($csfParams[$key][0])) ? $csfParams[$key][0] : null
	                            ));
	                        }
	                        break;
	                    case CustomSearchFieldUtil::TYPE_RADIO:
	                        if(!isset($field["option"][SOYSHOP_PUBLISH_LANGUAGE])) {
								$isContinue = true;
								break;
							}

	                        if(strlen($field["option"][SOYSHOP_PUBLISH_LANGUAGE])){
	                            foreach(explode("\n", $field["option"][SOYSHOP_PUBLISH_LANGUAGE]) as $i => $o){
	                                $o = trim($o);    //改行を除く
	                                if(!strlen($o)) continue;

	                                if(isset($field["default"]) && $field["default"] == 1){
	                                    $selected = ((!isset($csfParams[$key]) && $i === 0) || (isset($csfParams[$key]) && $o === $csfParams[$key]));
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

									$obj->addCheckBox("custom_search_" . $key . "_checkbox_" .$i, array(
	                                    "soy2prefix" => $prefix,
	                                    "type" => "checkbox",
	                                    "name" => $name . "[" . $key . "][]",
	                                    "value" => $o,
	                                    "selected" => (isset($csfParams[$key]) && is_array($csfParams[$key]) && in_array($o, $csfParams[$key])),
	                                    "label" => $o,
	                                    "elementId" => "custom_search_" . $key . "_" . $i
	                                ));
	                            }
	                        }
	                        break;
	                    case CustomSearchFieldUtil::TYPE_SELECT:
						    if(!isset($field["option"][SOYSHOP_PUBLISH_LANGUAGE])) {
								$isContinue = true;
								break;
							}

	                        $options = array();
	                        foreach(explode("\n", $field["option"][SOYSHOP_PUBLISH_LANGUAGE]) as $o){
	                            $options[trim($o)] = trim($o);
							}
							$obj->addSelect("custom_search_" . $key, array(
	                            "soy2prefix" => $prefix,
	                            "name" => $name . "[" . $key . "]",
	                            "options" => $options,
	                            "selected" => (isset($csfParams[$key])) ? $csfParams[$key] : false
	                        ));
	                        break;
	                    default:
	                        $obj->addInput("custom_search_" . $key, array(
	                            "soy2prefix" => $prefix,
	                            "name" => $name . "[" . $key . "]",
	                            "value" => (isset($csfParams[$key]) && is_string($csfParams[$key])) ? $csfParams[$key] : null
	                        ));
	                }
					if(!$isContinue) continue;
				}
			}

			//簡易予約カレンダー
			if(SOYShopPluginUtil::checkIsActive("reserve_calendar")){
				foreach(array("start", "end") as $t){
					$obj->addInput("custom_search_reserve_calendar_" . $t, array(
						"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
						"type" => "text",
						"name" => "c_search[reserve_calendar_" . $t . "]",
						"value" => (isset($params["reserve_calendar_" . $t]) && strlen($params["reserve_calendar_" . $t])) ? $params["reserve_calendar_" . $t] : null
					));
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
    }

    $obj->display();
}

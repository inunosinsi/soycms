<?php

function soyshop_search_field(string $html, HTMLPage $htmlObj){
    $obj = $htmlObj->create("soyshop_search_field", "HTMLTemplatePage", array(
            "arguments" => array("soyshop_search_field", $html)
    ));

    SOY2::import("util.SOYShopPluginUtil");
    if(SOYShopPluginUtil::checkIsActive("user_custom_search_field")){

        SOY2::import("module.plugins.user_custom_search_field.util.UserCustomSearchFieldUtil");
		SOY2::import("module.plugins.user_group.util.UserGroupCustomSearchFieldUtil");

        //GETの値を変数に入れておく。そのうちページャ対応を行わなければならないため
        $params = (isset($_GET["u_search"])) ? $_GET["u_search"] : array();
        $gParams = (isset($_GET["g_search"])) ? $_GET["g_search"] : array();

        //顧客名
        $obj->addInput("custom_search_user_name", array(
                "soy2prefix" => UserCustomSearchFieldUtil::PLUGIN_PREFIX,
                "type" => "text",
                "name" => "u_search[name]",
                "value" => (isset($params["name"]) && strlen($params["name"])) ? $params["name"] : null
        ));

		//顧客フリガナ
		$obj->addInput("custom_search_user_reading", array(
                "soy2prefix" => UserCustomSearchFieldUtil::PLUGIN_PREFIX,
                "type" => "text",
                "name" => "u_search[reading]",
                "value" => (isset($params["reading"]) && strlen($params["reading"])) ? $params["reading"] : null
        ));

        //メールアドレス
        $obj->addInput("custom_search_mail_address", array(
                "soy2prefix" => UserCustomSearchFieldUtil::PLUGIN_PREFIX,
                "type" => "text",
                "name" => "u_search[mail_address]",
                "value" => (isset($params["mail_address"]) && strlen($params["mail_address"])) ? $params["mail_address"] : null
        ));

		if(SOYShopPluginUtil::checkIsActive("user_group")){
			//グループ名
			$obj->addInput("custom_search_group_name", array(
	                        "soy2prefix" => UserGroupCustomSearchFieldUtil::PLUGIN_PREFIX,
	                        "type" => "text",
	                        "name" => "g_search[name]",
	                        "value" => (isset($gParams["name"]) && strlen($gParams["name"])) ? $gParams["name"] : null
	                ));

			//グループコード
			$obj->addInput("custom_search_group_code", array(
	                        "soy2prefix" => UserGroupCustomSearchFieldUtil::PLUGIN_PREFIX,
	                        "type" => "text",
	                        "name" => "g_search[code]",
	                        "value" => (isset($gParams["code"]) && strlen($gParams["code"])) ? $gParams["code"] : null
	                ));
		}

            //カスタムサーチフィールドとカテゴリカスタムフィールドの検索用フォームを出力
        foreach(array("user", "group") as $mode){
			if(!SOYShopPluginUtil::checkIsActive("user_group")) continue;
            switch($mode){
                case "group":
                    $configs = UserGroupCustomSearchFieldUtil::getConfig();
                    $prefix = UserGroupCustomSearchFieldUtil::PLUGIN_PREFIX;
                    $name = "g_search";
                    $params = $gParams;
                    break;
                default:
                    $configs = UserCustomSearchFieldUtil::getConfig();
                    $prefix = UserCustomSearchFieldUtil::PLUGIN_PREFIX;
                    $name = "u_search";
                    //paramはそのまま
            }

	    	if(count($configs)){
                foreach($configs as $key => $field){
                    switch($field["type"]){
                        case UserCustomSearchFieldUtil::TYPE_RANGE:
                            foreach(array("start", "end") as $t){
                                $obj->addInput("custom_search_" . $key . "_" . $t, array(
                                    "soy2prefix" => $prefix,
                                    "type" => "number",
                                    "name" => $name . "[" . $key . "_" . $t . "]",
                                    "value" => (isset($params[$key . "_" . $t]) && strlen($params[$key . "_" . $t])) ? (int)$params[$key . "_" . $t] : null
                            	));
                            }
                            break;
                        case UserCustomSearchFieldUtil::TYPE_CHECKBOX:
                            if(strlen($field["option"])){
                                $opt = array();
                                foreach(explode("\n", $field["option"]) as $i => $o){
                                    $o = trim($o);        //改行を除く
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
                    	case UserCustomSearchFieldUtil::TYPE_RADIO:
                            if(strlen($field["option"])){
                                foreach(explode("\n", $field["option"]) as $i => $o){
                                    $o = trim($o);        //改行を除く
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
                    	case UserCustomSearchFieldUtil::TYPE_SELECT:
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
                }
            }
        }
    }

    $obj->display();
}

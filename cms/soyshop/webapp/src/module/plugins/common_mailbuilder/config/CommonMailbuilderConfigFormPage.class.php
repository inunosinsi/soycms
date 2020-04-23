<?php
class CommonMailbuilderConfigFormPage extends WebPage{

    function __construct() {
        SOY2::import("module.plugins.common_mailbuilder.common.CommonMailbuilderCommon");
        SOY2::import("util.SOYShopPluginUtil");
    }

    function doPost(){

        if(soy2_check_token() && isset($_POST["content"])){

            foreach($_POST["content"] as $key => $content){
                CommonMailbuilderCommon::saveMailContent($content, $key);
            }

            if(isset($_POST["Sort"])){
                CommonMailbuilderCommon::saveSortConfig($_POST["Sort"]);
            }

            SOY2PageController::jump("Config.Detail?plugin=common_mailbuilder&updated");
        }

        SOY2PageController::jump("Config.Detail?plugin=common_mailbuilder&failed");
    }

    function execute(){

        parent::__construct();

        $this->addForm("form");

        /* sort */
        $sortConfig = CommonMailbuilderCommon::getSortConfig();
        $this->createAdd("sort_list", "HTMLList", array(
            "list" => array(
                "name" => "商品名",
                "code" => "商品コード",
                "cdate" => "作成日",
                "udate" => "更新日"
            ),
            'populateItem:function($entity,$key)' =>
                    '$this->createAdd("sort_input","HTMLCheckbox", array(' .
                        '"name" => "Sort[defaultSort]",' .
                        '"value" => $key,' .
                        '"label" => $entity,' .
                        '"selected" => ($key == "' . $sortConfig["defaultSort"] . '")' .
                    '));'
        ));

        $this->addCheckBox("sort_normal", array(
            "name" => "Sort[isReverse]",
            "selected" => (!$sortConfig["isReverse"]),
            "value" => 0,
            "label" => "昇順",
        ));

        $this->addCheckBox("sort_reverse", array(
            "name" => "Sort[isReverse]",
            "selected" => ($sortConfig["isReverse"]),
            "value" => 1,
            "label" => "降順",
        ));

        $this->addTextArea("mail_user", array(
            "name" => "content[user]",
            "value" => CommonMailbuilderCommon::getMailContent("user")
        ));

        $this->addTextArea("mail_admin", array(
            "name" => "content[admin]",
            "value" => CommonMailbuilderCommon::getMailContent("admin")
        ));

        //簡易予約カレンダー連携
        $this->addModel("installed_reserve_calendar", array(
            "visible" => SOYShopPluginUtil::checkIsActive("reserve_calendar")
        ));

		//置換文字列の拡張
		$this->createAdd("replace_string_list", "_common.Config.ReplaceStringListComponent", array(
			"list" => self::_getReplaceStringList()
		));
    }

	private function _getReplaceStringList(){
		SOYShopPlugin::load("soyshop.order.mail.replace");
		$values = SOYShopPlugin::invoke("soyshop.order.mail.replace",array("mode" => "strings"))->getStrings();
		if(!count($values)) return array();

		$list = array();
		foreach($values as $strings){
			if(!is_array($strings) || !count($strings)) continue;
			foreach($strings as $replace => $v){
				if(!strlen($replace) || !strlen($v)) continue;
				$list[$replace] = $v;
			}
		}

		return $list;
	}

    function setConfigObj($obj) {
        $this->config = $obj;
    }
}

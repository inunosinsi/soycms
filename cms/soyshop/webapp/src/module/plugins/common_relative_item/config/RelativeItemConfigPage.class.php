<?php

class RelativeItemConfigPage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.common_relative_item.util.RelativeItemUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			RelativeItemUtil::saveConfig($_POST["Page"]);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		$config = RelativeItemUtil::getConfig();

		parent::__construct();

		$this->addForm("form");

		/* sort */
		$this->addList("sort_list", array(
			"list" => array(
				"add" => "追加順",
				"name" => "商品名",
				"code" => "商品コード",
				"stock" => "在庫数",
				"price" => "販売価格",
				"cdate" => "作成日",
				"udate" => "更新日",
                "random" => "ランダム"
			),
			'populateItem:function($entity,$key)' =>
					'$this->createAdd("sort_input","HTMLCheckbox", array(' .
						'"name" => "Page[defaultSort]",' .
						'"value" => $key,' .
						'"label" => $entity,' .
						'"selected" => ($key == "'.$config["defaultSort"].'")' .
					'));'
		));

		$this->addCheckBox("sort_normal", array(
			"name" => "Page[isReverse]",
			"selected" => (!$config["isReverse"]),
			"value" => 0,
			"label" => "昇順",
		));

		$this->addCheckBox("sort_reverse", array(
			"name" => "Page[isReverse]",
			"selected" => ($config["isReverse"]),
			"value" => 1,
			"label" => "降順",
		));

	}

	function setConfigObj($obj) {
		$this->configObj = $obj;
	}
}

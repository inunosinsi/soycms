<?php

class NewItemConfigPage extends WebPage{
	
	private $configObj;
	
	function NewItemConfigPage(){
		SOY2::import("module.plugins.common_new_item.util.NewItemUtil");
	}
	
	function doPost(){
		if(soy2_check_token()){
			
			NewItemUtil::saveConfig($_POST["Page"]);
			$this->configObj->redirect("updated");
		}
	}
		
	function execute(){
		$config = NewItemUtil::getConfig();
		
		WebPage::WebPage();
		
		$this->addForm("form");
		
		/* sort */
		$this->addList("sort_list", array(
			"list" => array(
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

        $this->addInput("try_count", array(
            "name" => "Page[tryCount]",
            "value" => (isset($config["tryCount"])) ? (int)$config["tryCount"] : 3
        ));
		
	}
	
	function setConfigObj($obj) {
		$this->configObj = $obj;
	}
		
}
?>
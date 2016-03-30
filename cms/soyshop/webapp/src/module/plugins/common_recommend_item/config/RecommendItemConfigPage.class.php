<?php

class RecommendItemConfigPage extends WebPage{
	
	private $configObj;
	
	function RecommendItemConfigPage(){
		SOY2::import("module.plugins.common_recommend_item.util.RecommendItemUtil");
	}
	
	function doPost(){
		if(soy2_check_token()){
			
			RecommendItemUtil::saveConfig($_POST["Page"]);
			$this->configObj->redirect("updated");
		}
	}
		
	function execute(){
		$config = RecommendItemUtil::getConfig();
		
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
				"udate" => "更新日"
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
?>
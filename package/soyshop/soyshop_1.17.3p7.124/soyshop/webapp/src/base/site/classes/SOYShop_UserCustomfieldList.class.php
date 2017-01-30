<?php
class SOYShop_UserCustomfieldList extends HTMLList{
	
	protected function populateItem($entity,$key,$counter,$length){
		
		//フィールド名 ラベル
		$this->addLabel("customfield_name", array(
			"text" => (isset($entity["name"])) ? $entity["name"] : ""
		));
		
		$this->addModel("customfield_is_required", array(
			"visible" => (isset($entity["isRequired"]) && $entity["isRequired"] == SOYShop_UserAttribute::IS_REQUIRED),
			"class" => "require"
		));
		
		//フォーム部品 ラベル
		$this->createAdd("customfield_form","HTMLLabel", array(
			"html" => (isset($entity["form"])) ? $entity["form"] : ""
		));
		
		//値 ラベル
		$this->addLabel("customfield_confirm", array(
			"html" => (isset($entity["confirm"])) ? $entity["confirm"] : ""
		));
		
		$this->addModel("has_customfield_error", array(
			"visible" => (isset($entity["error"]) && strlen($entity["error"]) > 0)
		));
		$this->addLabel("customfield_error", array(
			"text" => (isset($entity["error"])) ? $entity["error"] : ""
		));


	}
}
?>
<?php

class ItemOptionLogic extends SOY2LogicBase{
	
	function getOptions(){
		$options = SOYShop_DataSets::get("item_option", null);
		return (isset($options)) ? soy2_unserialize($options) : array();
	}
	
	function getTypes(){
		return array("select" => "セレクトボックス", "radio" => "ラジオボタン");
	}
}
?>
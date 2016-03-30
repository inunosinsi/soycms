<?php

SOY2HTMLFactory::importWebPage("_base.TreeComponent");
class MyTreeComponent extends TreeComponent{

	private $selected;
	private $func;

	function getOnclick($id){
		if($this->func){
			return $this->func.'('.$id.',this);';
		}else{
			return 'onClickLeaf('.$id.',this);';
		}
	}

	function getClass($id){
		if(is_array($this->selected) && in_array($id,$this->selected)){
			return "selected_category";
		}else{
			return "";
		}
	}

	function getSelected() {
		return $this->selected;
	}
	function setSelected($selected) {
		$this->selected = $selected;
	}
	function getFunc(){
		return $this->func;
	}
	function setFunc($func){
		$this->func = $func;
	}
}
?>
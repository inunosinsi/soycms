<?php

class CategorySelectComponent extends HTMLSelect{

	private $domId;
	private $label;

    function getStartTag(){
		return $this->getWrapperStart() . parent::getStartTag() . $this->getWrapperEnd();

	}

	function getWrapperStart(){

		$html = array();

		$domText = '<?php echo $'.$this->getPageParam().'["'.$this->getId().'_attribute"]["id"]; ?>';

		$id = ($this->domId) ? $domText : $this->getAttribute("id");

		return implode("",$html);
	}

	function getWrapperEnd(){
		return "";
	}

	function execute(){

		$this->setOptions(array());

		//嘘を設定
		if(strlen($this->selected) > 0){
			$this->setOptions(array($this->selected =>$this->label));
		}

		$this->setAttribute("onmouseup","return show_category_select(this);");

		parent::execute();
	}

	function getDomId() {
		return $this->domId;
	}
	function setDomId($domId) {
		$this->domId = $domId;
		$this->setAttribute("id",$domId);
	}

	function getLabel() {
		return $this->label;
	}
	function setLabel($label) {
		$this->label = $label;
	}
}

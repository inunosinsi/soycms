<?php

class TrackbackURLComponent extends HTMLLabel{

	function execute(){

		parent::execute();

		if($this->tag == "input"){
			$this->setInnerHTML("");
		}else{
			$this->clearAttribute("value");
		}
	}
}

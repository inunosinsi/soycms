<?php

class MultiLanguageReplacementListComponent extends HTMLList {

	private $multiLangConf;
	private $symbol;

	protected function populateItem($lang){
		if(!is_string($lang)) $lang = "jp";
		
		$this->addLabel("language_label", array(
			"text" => $lang
		));

		$this->addInput("language_string_input", array(
			"name" => "language[".$this->symbol."][".$lang."]",
			"value" => (strlen($this->symbol) && $lang != "jp" && isset($this->multiLangConf[$this->symbol][$lang])) ? $this->multiLangConf[$this->symbol][$lang] : ""
		));
	}

	function setMultiLangConf($multiLangConf){
		$this->multiLangConf = $multiLangConf;
	}
	function setSymbol($symbol){
		$this->symbol = $symbol;
	}
}
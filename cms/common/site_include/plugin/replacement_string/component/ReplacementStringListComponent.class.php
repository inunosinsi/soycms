<?php

class ReplacementStringListComponent extends HTMLList{
	
	private $multiLangs;
	private $multiLangConf;

	protected function populateItem($entity, $key){
		
		$sym = (isset($entity["symbol"])) ? $entity["symbol"] : "";
		$this->addLabel("symbol", array(
			"text" => $sym
		));

		$this->addLabel("string", array(
			"text" => (isset($entity["string"])) ? $entity["string"] : ""
		));
		
		$this->addInput("string_input", array(
			"name" => "string[" . $key . "]",
			"value" => (isset($entity["string"])) ? $entity["string"] : ""
		));

		$this->addLabel("jp_label", array(
			"html" => count($this->multiLangs) ? "<label>jp</label>:" : ""
		));

		$this->createAdd("multi_language_list", "MultiLanguageReplacementListComponent", array(
			"list" => $this->multiLangs,
			"symbol" => $sym,
			"multiLangConf" => $this->multiLangConf
		));
		
		$this->addLink("remove_link", array(
			"link" => "?replacement_string&remove=" . $key . "#config",
			"onclick" => "return confirm('削除しますか？');"
		));
	}

	function setMultiLangs($multiLangs){
		$this->multiLangs = $multiLangs;
	}
	function setMultiLangConf($multiLangConf){
		$this->multiLangConf = $multiLangConf;
	}
}
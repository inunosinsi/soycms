<?php

class SamplePage extends WebPage {

	private $hash;

	function __construct(){
		SOY2::import("site_include.plugin.multiple_page_form.util.MPFRouteUtil");
	}

	/**
	 * ※soy2_check_tokenは既に済んでいる
	 * データをセッションに入れる方法は
	 * array(
	 *	array(label, value),
	 *	...
	 * )
	 * とする
	 */
	function doPost(){
		$values = array();
		$values[] = array("label" => "hoge", "value" => "ほげ");
		$values[] = array("label" => "huga", "value" => "ふが");
		MPFRouteUtil::save($this->hash, $values);
	}

	function execute(){
		parent::__construct();

		$values = MPFRouteUtil::getValues($this->hash);

		$this->addForm("form");
	}

	function setHash($hash){
		$this->hash = $hash;
	}
}

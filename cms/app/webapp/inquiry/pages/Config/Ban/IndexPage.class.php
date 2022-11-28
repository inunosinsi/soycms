<?php

class IndexPage extends WebPage {

	function doPost(){
		if(soy2_check_token()){
			$ban = $_POST["Ban"];
			$excludeList = (isset($ban["excludeList"]) && strlen($ban["excludeList"])) ? $ban["excludeList"] : null;
			$cnt = (isset($ban["count"]) && is_numeric($ban["count"])) ? (int)$ban["count"] : 30;
			$release = (isset($ban["release"]) && is_numeric($ban["release"])) ? (int)$ban["release"] : 3;

			SOYInquiry_DataSets::put("execlude_ip_address_list", $excludeList);
			SOYInquiry_DataSets::put("form_ban_count", $cnt);
			SOYInquiry_DataSets::put("form_ban_release", $release);

			CMSApplication::jump("Config.Ban?updated");
			exit;
		}
	}

	function __construct(){
		SOY2::import("domain.SOYInquiry_DataSetsDAO");

		parent::__construct();

		DisplayPlugin::toggle("updated", isset($_GET["updated"]));

		$this->addForm("form");

		$this->addInput("exclude_list", array(
			"name" => "Ban[excludeList]",
			"value" => SOYInquiry_DataSets::get("execlude_ip_address_list", null),
			"attr:placeholder" => "127.0.0.1"
		));

		$this->addLabel("remote_addr", array(
			"text" => (isset($_SERVER["REMOTE_ADDR"])) ? $_SERVER["REMOTE_ADDR"] : ""
		));

		$this->addInput("count", array(
			"name" => "Ban[count]",
			"value" => SOYInquiry_DataSets::get("form_ban_count", 30),
			"style" => "width:80px;",
			"attr:required" => "required"
		));

		$this->addInput("release", array(
			"name" => "Ban[release]",
			"value" => SOYInquiry_DataSets::get("form_ban_release", 3),
			"style" => "width:80px;",
			"attr:required" => "required"
		));
	}
}

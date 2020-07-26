<?php

class IconListComponent extends HTMLList{

	function populateItem($file){
		$this->addImage("icon", array(
			"src" => self::_url() . "/" . $this->iconDir . "/" . $file
		));

		$this->addCheckBox("delete", array(
			"name" => "deletes[]",
			"value" => $file
		));

		if(!is_string($file) || strpos($file, ".") === 0) return false;
	}

	function _url(){
		static $url;
		if(is_null($url)){
			$url = UserInfoUtil::getSiteURL();
			$url = substr($url, 0, strrpos($url, "/"));
		}
		return $url;
	}

	function setIconDir($iconDir){
		$this->iconDir = $iconDir;
	}
}

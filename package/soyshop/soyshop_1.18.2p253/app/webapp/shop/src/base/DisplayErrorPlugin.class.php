<?php

class DisplayErrorPlugin extends DisplayPlugin{

    var $soyValue;
    public static $errors = array();

	function executePlugin($soyValue){
		$this->soyValue = $soyValue;
	}

	function getStartTag(){
		return '<?php if(DisplayErrorPlugin::check("'.$this->soyValue.'")){ ?>'. parent::getStartTag();
	}

	function getEndTag(){
		return  parent::getEndTag() . '<?php } ?>';
	}

	public static function check($soyValue){
		$array = self::$errors;

		return (isset($array[$soyValue])) ? (boolean)$array[$soyValue] : false; //default is hidden
	}

	public static function setErrors($errors){
		self::$errors = $errors;
	}

	public static function setError($key,$value){
		self::$errors[$key] = (boolean)$value;
	}
}

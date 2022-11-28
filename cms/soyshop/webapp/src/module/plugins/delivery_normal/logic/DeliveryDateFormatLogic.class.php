<?php

class DeliveryDateFormatLogic extends SOY2LogicBase{

	private $w = array("日", "月", "火", "水", "木", "金", "土");

	function convertDateString($format, $timestamp){
		if(strpos($format, "#w#") !== false){
			$w = $this->w[date("w", $timestamp)];
			$format = str_replace("#w#", $w, $format);
		}

		return date($format, $timestamp);
	}

	function getDateFormat(){
		return "yy年mm月dd日";
	}
}

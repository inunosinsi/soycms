<?php

class DeliveryClickPostSearch extends SOYShopOrderSearch{

	function __construct(){
		SOY2::import("module.plugins.delivery_clickpost.util.ClickPostUtil");
	}

	function setParameter(array $params){
		$param = SOYShopPluginUtil::convertArray2String($params);
		if((int)$param === 0) return array();
		
		return array(
			"queries" => array("attributes LIKE '%delivery_clickpost%'"),
			"binds" => array()
		);
	}

	function searchItems(array $params){
		$param = SOYShopPluginUtil::convertArray2String($params);
		$cfg = ClickPostUtil::getConfig();
		$name = (isset($cfg["name"])) ? trim($cfg["name"]) : "郵送";

		$form = "<input type=\"hidden\" name=\"search[customs][delivery_clickpost]\" value=\"0\">";
		$form .= "<label>";
		if((int)$param === 1){
			$form .= "<input type=\"checkbox\" name=\"search[customs][delivery_clickpost]\" value=\"1\" checked>";
		}else{
			$form .= "<input type=\"checkbox\" name=\"search[customs][delivery_clickpost]\" value=\"1\">";
		}
		$form .= "配送方法が「".$name."」の注文に絞る</label>";

		return array(
			"label" => $name,
			"form" => $form
		);
	}
}
SOYShopPlugin::extension("soyshop.order.search", "delivery_clickpost", "DeliveryClickPostSearch");

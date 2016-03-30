<?php
/*
 * Created on 2012/05/21
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
class SOYInquiryConnectorCommon{
	
	public static function getConfig(){
		return SOYShop_DataSets::get("soyinquiry_connector_config", array(
										"url" => "http://example.com/inquiry"
									));
	}
}
?>
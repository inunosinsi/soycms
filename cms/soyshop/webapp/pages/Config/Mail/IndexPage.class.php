<?php 
/**
 * @class Config.Mail.IndexPage
 * @date 2009-07-30T18:00:46+09:00
 * @author SOY2HTMLFactory
 */ 
class IndexPage extends WebPage{
	
	function __construct(){
		parent::__construct();
		
		$this->createAdd("mail_plugin_list", "_common.Plugin.MailPluginListComponent", array(
    		"list" => self::getMailPluginList()
    	));

	}
	
	private function getMailPluginList(){
    	SOYShopPlugin::load("soyshop.order.detail.mail");
    	$delegate = SOYShopPlugin::invoke("soyshop.order.detail.mail", array(
    	
    	));
    	
    	if(!count($delegate->getList())) return array();
    	
    	$list = array();
    	foreach($delegate->getList() as $values){
    		if(!is_array($values)) continue;
   			foreach($values as $value){
   				$list[] = $value;
   			}
    	}
    	
    	return $list;
    }
}


?>
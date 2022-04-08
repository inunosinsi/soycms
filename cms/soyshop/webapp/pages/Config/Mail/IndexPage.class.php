<?php
/**
 * @class Config.Mail.IndexPage
 * @date 2009-07-30T18:00:46+09:00
 * @author SOY2HTMLFactory
 */
class IndexPage extends WebPage{

	function __construct(){
		parent::__construct();

		$this->addLabel("user_label", array("text" => SHOP_USER_LABEL));

		$this->createAdd("mail_plugin_list", "_common.Plugin.MailPluginListComponent", array(
    		"list" => self::_getMailPluginList("order"),
			"mode" => "order"
    	));

		$this->createAdd("user_mail_plugin_list", "_common.Plugin.MailPluginListComponent", array(
    		"list" => self::_getMailPluginList("user"),
			"mode" => "user"
    	));
	}

	private function _getMailPluginList($mode="order"){
    	SOYShopPlugin::load("soyshop.order.detail.mail");
    	$mailList = SOYShopPlugin::invoke("soyshop.order.detail.mail", array("mode" => $mode))->getList();
    	if(!count($mailList)) return array();

    	$list = array();
    	foreach($mailList as $arr){
    		if(!is_array($arr)) continue;
   			foreach($arr as $v){
   				$list[$v["id"]] = $v["title"];
   			}
    	}

    	return $list;
    }

	function getBreadcrumb(){
		return BreadcrumbComponent::build("メール設定", array("Config" => "設定"));
	}
}

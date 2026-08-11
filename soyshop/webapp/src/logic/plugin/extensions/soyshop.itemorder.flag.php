<?php

class SOYShopItemOrderFlag implements SOY2PluginAction{

	/**
	 * mailにメールタイプを指定すれば自動送信メール @ToDo mailに値を指定したら、ステータス変更時に自動送信メール
	 * @return array(ステータスコード => ラベル))
	 */
	function flagItem(){}
}
class SOYShopItemOrderFlagDeletageAction implements SOY2PluginDelegateAction{

	private $_list = array();

	function run($extetensionId, $moduleId, SOY2PluginAction $action){
		if($action instanceof SOYShopItemOrderFlag){
			$this->_list[$moduleId] = $action->flagItem();
		}
	}

	function getList(){
		return $this->_list;
	}
}
SOYShopPlugin::registerExtension("soyshop.itemorder.flag", "SOYShopItemOrderFlagDeletageAction");

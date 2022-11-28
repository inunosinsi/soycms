<?php

class SOYShopOrderStatusSort implements SOY2PluginAction{

	/**
	 * 注文状態の並び順を返す
	 * @return array(ステータスコード)
	 */
	function statusSort(){
		return array();
	}

	/**
	 * 支払い状態の並び順を返す
	 * @return array(ステータスコード)
	 */
	function paymentStatusSort(){
		return array();
	}
}
class SOYShopOrderStatusSortDeletageAction implements SOY2PluginDelegateAction{

	private $mode = "status";
	private $_sort = array();

	function run($extetensionId, $moduleId, SOY2PluginAction $action){
		//プラグインは一つだけを想定
		if($action instanceof SOYShopOrderStatusSort){
			switch($this->mode){
				case "status":
					$sort = $action->statusSort();
					if(is_array($sort) && count($sort)){
						$this->_sort = $sort;
					}
					break;
				case "payment":
					$sort = $action->paymentStatusSort();
					if(is_array($sort) && count($sort)){
						$this->_sort = $sort;
					}
					break;
			}
		}
	}

	function getSort(){
		return $this->_sort;
	}

	function getMode(){
		return $this->mode;
	}
	function setMode($mode){
		$this->mode = $mode;
	}
}
SOYShopPlugin::registerExtension("soyshop.order.status.sort", "SOYShopOrderStatusSortDeletageAction");

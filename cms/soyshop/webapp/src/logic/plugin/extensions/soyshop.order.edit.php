<?php
class SOYShopOrderEditBase implements SOY2PluginAction{

	//HTMLを自由に記述出来るスペース
	function html(){
		return "";
	}

	function addFunc($orderId){
		return "";
	}

	function addFuncOnAdminOrder($orderId){
		return "";
	}
}

class SOYShopOrderEditBaseDeletageAction implements SOY2PluginDelegateAction{

	private $mode = "html";
	private $orderId;
	private $_html;

	function run($extetensionId,$moduleId,SOY2PluginAction $action){
		switch($this->mode){
			case "item":	//注文編集画面で注文商品の編集の画面の下に自由に拡張できる
				$html = $action->addFunc($this->orderId);
				break;
			case "order":	//管理画面からの注文で商品の選択を行う画面の下で自由に拡張できる
				$html = $action->addFuncOnAdminOrder($this->orderId);
				break;
			case "html":	//注文詳細画面でjavascriptの記述等を追加する時に使用する
			default:
				$html = $action->html();
		}

		if(strlen($html)){
			if(strlen($this->_html)){
				$this->_html .= "\n";
			}
			$this->_html .= $html;
		}
	}

	function setMode($mode) {
		$this->mode = $mode;
	}
	function setOrderId($orderId){
		$this->orderId = $orderId;
	}

	function getHTML(){
		return $this->_html;
	}
}
SOYShopPlugin::registerExtension("soyshop.order.edit",      "SOYShopOrderEditBaseDeletageAction");

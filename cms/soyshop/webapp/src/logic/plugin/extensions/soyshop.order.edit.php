<?php
class SOYShopOrderEditBase implements SOY2PluginAction{

	private $orderId;

	//HTMLを自由に記述出来るスペース
	function html(){
		return "";
	}

	//注文詳細(Detailの方)ページでHTMLを自由に記述できるスペース
	function html_on_detail(){
		return "";
	}

	function addFunc(int $orderId){
		return "";
	}

	function addFuncOnAdminOrder(int $orderId){
		return "";
	}

	function update(int $orderId, bool $isChange){}

	function error(int $orderId){}

	/**
	 * @return array("message" => "", "alert" => "danger or success") // bootstrap's alerts
	 */
	function message(int $orderId){
		return array();
	}

	/**
	 * @return array({attribute_id} => array({name} => string, {value} => string, {hidden} => boolean, {readonly} => boolen))
	 */
	function addAttributes(){
		return array();
	}

	function getOrderId(){
		return (is_numeric($this->orderId)) ? (int)$this->orderId : 0;
	}
	function setOrderId($orderId){
		$this->orderId = $orderId;
	}
}

class SOYShopOrderEditBaseDeletageAction implements SOY2PluginDelegateAction{

	private $mode = "html";
	private $orderId;
	private $isChange = true;
	private $_html = "";
	private $_attributes = array();
	private $_messages = array();

	function run($extetensionId,$moduleId,SOY2PluginAction $action){
		$html = "";
		switch($this->mode){
			case "item":	//注文編集画面で注文商品の編集の画面の下に自由に拡張できる
				$html = $action->addFunc((int)$this->orderId);
				break;
			case "order":	//管理画面からの注文で商品の選択を行う画面の下で自由に拡張できる
				$html = $action->addFuncOnAdminOrder((int)$this->orderId);
				break;
			case "attribute":	//管理画面の注文編集でattributeを増やす
				$attrs = $action->addAttributes();
				if(is_array($attrs) && count($attrs)) {
					$this->_attributes[$moduleId] = $attrs;
				}
				break;
			case "html_on_detail":
				$action->setOrderId((int)$this->orderId);	//使うかもしれない
				$html = $action->html_on_detail();
				break;
			case "html":	//注文詳細画面でjavascriptの記述等を追加する時に使用する
				$action->setOrderId((int)$this->orderId);
				$html = $action->html();
				break;
			case "update":
				$action->update((int)$this->orderId, $this->isChange);
				break;
			case "error":
				$action->error((int)$this->orderId);
				break;
			case "message":
			default:
				$msg = $action->message((int)$this->orderId);
				if(is_array($msg) && isset($msg["message"])){
					$this->_messages[$moduleId] = $msg;
				}
				break;
		}

		if(is_string($html) && strlen($html)){
			if(strlen($this->_html)) $this->_html .= "\n";
			$this->_html .= $html;
		}
	}

	function setMode($mode) {
		$this->mode = $mode;
	}
	function setOrderId($orderId){
		$this->orderId = $orderId;
	}
	function setIsChange($isChange){
		$this->isChange = $isChange;
	}

	function getHTML(){
		return $this->_html;
	}

	function getAttributes(){
		return $this->_attributes;
	}
	function getMessages(){
		return $this->_messages;
	}
}
SOYShopPlugin::registerExtension("soyshop.order.edit",      "SOYShopOrderEditBaseDeletageAction");

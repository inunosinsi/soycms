<?php
class SOYShopOrderCustomfield implements SOY2PluginAction{

	private $cart;
	private $mypage;

	/**
	 * セッションの削除
	 */
	function clear(CartLogic $cart){
	}

	/**
	 * @param array $param 中身は$_POST["customfield_module"]
	 */
	function doPost(array $param){
	}

	/**
	 * 注文前に何らかの処理を行う
	 * @param CartLogic $cart
	 */
	function order(CartLogic $cart){
	}

	/**
	 * 注文後に何らかの処理を行う
	 * @param CartLogic $cart
	 */
	function complete(CartLogic $cart){
	}

	/**
	 * エラーチェック
	 * @return Boolean
	 */
	function hasError(array $param){
		return false;
	}

	/**
	 * カートで表示するフォーム
	 * ["name"]、["description"],["error"]を返す
	 * @param CartLogic $cart
	 * @return Array("name" => "", "description" => "", "error" => "")
	 */
	function getForm(CartLogic $cart){
	}

	/**
	 * 管理画面で表示する値
	 * ["name"]、["value"]を返す
	 * @param Integer OrderId
	 * @return Array array(array("name" => "", "value" => "", "style" => "")) ※styleはなしで良い
	 */
	function display(int $orderId){

	}

	/**
	 * @param int $orderID
	 * @return Array labelとformの連想配列を格納 array(array("label" => "", "form" => ""))
	 */
	function edit(int $orderId){

	}

	/**
	 * @param int $orderID
	 * @return boolen
	 */
	function error(int $orderId){
		return false;
	}

	function getErrorMessage(){
		return "";
	}

	/**
	 * saveする際のconfigを取得して返す
	 */
	function config(int $orderId){

	}

	function getCart() {
		return $this->cart;
	}
	function setCart($cart) {
		$this->cart = $cart;
	}

	function getMyPage(){
		return $this->mypage;
	}
	function setMyPage($mypage){
		$this->mypage = $mypage;
	}
}
class SOYShopOrderCustomfieldDeletageAction implements SOY2PluginDelegateAction{

	private $mode = "list";
	private $cart;
	private $mypage;
	private $param;//$_POST["customfield_module"]
	private $orderId;

	private $_list = array();
	private $_display = array();
	private $_label = array();
	private $hasError = false;
	private $errorMessage = "";

	function run($extetensionId, $moduleId, SOY2PluginAction $action){

		//カートは必要
//		if(!$this->getCart()){
//			throw new Exception("soyshop.order.customfield needs cart information.");
//		}

		$action->setCart($this->getCart());
		$action->setMyPage($this->getMyPage());

		switch($this->mode){
			case "list":
				$this->_list[$moduleId] = $action->getForm($this->cart);
				break;
			case "checkError":
				if($action->hasError($this->param)){
					$this->hasError = true;
				}else{
					//do nothing
				}
				break;
			case "clear":
				$action->clear($this->cart);
				break;
			case "post":
				$action->doPost($this->param);
				break;
			case "order":
				$action->order($this->cart);
				break;
			case "complete":
				$action->complete($this->cart);
				break;
			case "admin":
				$this->_display[$moduleId] = $action->display($this->orderId);
				break;
			case "edit":
				$this->_label[$moduleId] = $action->edit($this->orderId);
				break;
			case "error":
				if(is_numeric($this->orderId) && $action->error($this->orderId)){
					$this->hasError = true;
				}else{
					//do nothing
				}
				break;
			case "error_message":	//当面は上書きのみ
				$errMsg = $action->getErrorMessage();
				if(strlen($errMsg)) $this->errorMessage = $errMsg;
				break;
			case "config":
				$this->_list[$moduleId] = $action->config($this->orderId);
				break;
		}

	}

	function getList(){
		return $this->_list;
	}
	function getDisplay(){
		return $this->_display;
	}
	function getLabel(){
		return $this->_label;
	}

	function getMode() {
		return $this->mode;
	}
	function setMode($mode) {
		$this->mode = $mode;
	}
	function getCart() {
		return $this->cart;
	}
	function setCart($cart) {
		$this->cart = $cart;
	}
	function getMyPage(){
		return $this->mypage;
	}
	function setMyPage($mypage){
		$this->mypage = $mypage;
	}
	function getOrderId(){
		return (is_numeric($this->orderId)) ? (int)$this->orderId : 0;
	}
	function setOrderId($orderId){
		$this->orderId = $orderId;
	}
	function getParam() {
		return $this->param;
	}
	function setParam($param) {
		$this->param = $param;
	}
	function hasError(){
		return $this->hasError;
	}

	function getErrorMessage(){
		return $this->errorMessage;
	}
}
SOYShopPlugin::registerExtension("soyshop.order.customfield","SOYShopOrderCustomfieldDeletageAction");

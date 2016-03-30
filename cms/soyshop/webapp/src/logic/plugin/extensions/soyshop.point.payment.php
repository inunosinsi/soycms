<?php
class SOYShopPointPaymentBase implements SOY2PluginAction{
	
	private $cart;

	/**
	 * ポイント支払金額の計算とモジュールの登録
	 */
	function doPost($param, $userId){

	}
	
	function clear(){
		
	}
	
	/**
	 * 注文確定時に動作する
	 */
	function order(){
		
	}
	
	/**
	 * エラーチェック
	 * @return Boolean
	 */
	function hasError($param){
		return false;
	}
	
	/*
	 * エラーメッセージ
	 * @return string
	 */
	function getError(){
		return "";
	}

	/*
	 * カートで表示するモジュール名
	 * （空にするとそのモジュールは丸ごと表示されない）
	 */
	function getName(){
		return "";
	}

	/*
	 * カートで表示するフォーム
	 * name="discount_module[***]"
	 */
	function getDescription($userId){
		return "";
	}

	function getCart() {
		return $this->cart;
	}
	function setCart($cart) {
		$this->cart = $cart;
	}
}

class SOYShopPointPaymentDeletageAction implements SOY2PluginDelegateAction{

	private $mode = "list";
	private $cart;
	private $param;//$_POST["point_module"]
	
	private $_list = array();
	private $hasError = false;
	private $userId;

	function run($extetensionId, $moduleId, SOY2PluginAction $action){

		//カートは必要
		if(!$this->getCart()){
			throw new Exception("soyshop.point.payment needs cart information.");
		}

		$action->setCart($this->getCart());

		switch($this->mode){
			case "list":
				if(strlen($action->getName())){
					$this->_list[$moduleId] = array(
						"name"        => $action->getName(),
						"description" => $action->getDescription($this->userId),
						"error"       => $action->getError(),
					);
				}
				break;
			
			//ページのdoPost内で
			case "checkError":
				
				if($action->hasError(@$this->param[$moduleId])){
					$this->hasError = true;
				}else{
					//do nothing
				}
				break;
			
			//ページのdoPost内でエラーのないとき
			case "select":
				$action->doPost(@$this->param[$moduleId], $this->userId);
				break;
				
			case "clear":
				$action->clear();
				break;
			
			//注文処理後
			case "order":
				$action->order();
				break;
		}
	}

	function getList(){
		return $this->_list;
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
	function getUserId(){
		return $this->userId;
	}
	function setUserId($userId){
		$this->userId = $userId;
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
	
}
SOYShopPlugin::registerExtension("soyshop.point.payment", "SOYShopPointPaymentDeletageAction");
?>
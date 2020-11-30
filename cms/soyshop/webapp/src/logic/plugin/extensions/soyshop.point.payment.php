<?php
class SOYShopPointPaymentBase implements SOY2PluginAction{

	private $cart;

	/**
	 * パラメータの消去
	 * Cart03で選択肢を表示する前に呼び出される
	 */
	function clear(){

	}

	/*
	 * Cart03で表示するモジュール名
	 * （空にするとそのモジュールは丸ごと表示されない）
	 */
	function getName($userId){
		return "";
	}

	/*
	 * Cart03で表示するフォーム
	 * name="discount_module[***]"
	 */
	function getDescription($userId){
		return "";
	}

	/*
	 * Cart03の選択時に表示するエラーメッセージ
	 * @return string
	 */
	function getError($userId){
		return "";
	}

	/**
	 * エラーチェック
	 * Cart03->doPostで最初に実行される
	 * あまり意味はなさそう
	 * @return Boolean
	 */
	function hasError($param){
		return false;
	}

	/**
	 * ポイント支払金額の計算とモジュールの登録
	 * Cart03->doPostで選択されたときに実行される
	 */
	function doPost($param, $userId){

	}

	/**
	 * Cart04->doPostの注文確定時に動作する
	 */
	function order(){

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
				$name = $action->getName($this->userId);
				if(strlen($name)){
					$this->_list[$moduleId] = array(
						"name"        => $name,
						"description" => $action->getDescription($this->userId),
						"error"       => $action->getError($this->userId),//getDescriptionより後に呼び出す必要がある
					);
				}
				break;

			//ページのdoPost内で
			case "checkError":
				if(is_array($this->param) && is_string($moduleId) && isset($this->param[$moduleId])){
					if($action->hasError($this->param[$moduleId])){
						$this->hasError = true;
					}else{
						//do nothing
					}
				}

				break;

			//ページのdoPost内でエラーのないとき
			case "select":
				if(is_numeric($this->param) || is_string($this->param)){
					$param = $this->param;
				}else if(is_array($this->param) && is_string($moduleId) && isset($this->param[$moduleId])){
					$param = $this->param[$moduleId];
				}
				$action->doPost($param, $this->userId);
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

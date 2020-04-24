<?php
class SOYShopDiscount implements SOY2PluginAction{

	private $cart;

	/**
	 * 割引金額の計算とモジュールの登録
	 */
	function doPost($param){

	}
	
	function clear(){
		
	}
	
	/**
	 * 注文処理時にクーポンコードを使用済にする
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
	function getDescription(){
		return "";
	}

	function getCart() {
		return $this->cart;
	}
	function setCart($cart) {
		$this->cart = $cart;
	}

	/**
	 * 割引対象とするチェック
	 * @return boolean
	 */
	function checkAddList(){
		return true;
	}
}
class SOYShopDiscountDeletageAction implements SOY2PluginDelegateAction{

	private $mode = "list";
	private $cart;
	private $param;//$_POST["discount_module"]
	
	private $_list = array();
	private $hasError = false;

	function run($extetensionId,$moduleId,SOY2PluginAction $action){

		//カートは必要
		if(!$this->getCart()){
			throw new Exception("soyshop.discount needs cart information.");
		}

		$action->setCart($this->getCart());
		
		//割引の対象とならない場合
		if(!$action->checkAddList())return;
		
		
		switch($this->mode){
			case "list":
				if(strlen($action->getName())){
					$this->_list[$moduleId] = array(
						"name"        => $action->getName(),
						"description" => $action->getDescription(),
						"error"       => $action->getError(),
					);
				}
				break;
			
			//Cart03のdoPost内でセッションをクリア
			case "clear":
				$action->clear();
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
				$action->doPost(@$this->param[$moduleId]);
				break;
			
			//注文処理後：クーポンコードを使用済にする
			case "order":
				$action->order();
				break;
			
			//割引内容のcreateAdd()
			case "addDiscountLabel":
				$action->addDiscountLabel();
				break;
			
			//合計から割り引く
			case "discount":
				$action->discount();
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
SOYShopPlugin::registerExtension("soyshop.discount","SOYShopDiscountDeletageAction");
?>

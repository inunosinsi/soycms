<?php
class SOYShopDelivery implements SOY2PluginAction{

	private $cart;
	private $order;

	/**
	 * Cartで配送方法選択後にCartLogicに配送方法を登録する等の処理を行う
	 */
	function onSelect(CartLogic $cart){
		/**
		 * 配送方法を登録する
		 * $module = new SOYShop_ItemModule();
		 * $module->setId("delivery_******");		//プラグインIDを指定する
		 * $module->setType("delivery_module");		//他の配送モジュールを登録しないように必ず登録
		 * $module->setName($this->getName());		//支払い方法
		 * $module->setIsInclude(false);			//注文の合算に含めるか？内税の場合はfalseにする
		 * $module->setIsVisible(false);			//顧客に見せるか？
		 * $module->setPrice($this->getPrice());	//当モジュールを選択した時の加算する金額
		 * $cart->addModule($module);
		**/
	}

	/**
	 * @return string
	 * Cartの配送方法選択画面等で表示する支払い方法名
	 */
	function getName(){
		return "";
	}

	/**
	 * @return string
	 * Cartの配送方法選択画面で表示する支払い方法の説明文
	 */
	function getDescription(){
		return "";
	}

	/**
	 * @return integer
	 * Cart等で配送方法として選択した時に加算される金額。手数料等
	 */
	function getPrice(){
		return 0;
	}

	/**
	 * @return boolen
	 * Cartの支払い方法選択画面で選択項目として表示するか？
	 */
	function getMethod(CartLogic $cart, $moduleId){
		return true;
	}

	/**
	 * @return array(array("label" => "", "form" => "")...)
	 * マイページの注文詳細編集画面でフォームを出力
	 */
	function edit(){
		return array();
	}

	/**
	 * マイページの注文詳細編集でPOST後に実行される
	 */
	function update(){

	}

	/**
	 * 管理画面の注文編集画面で設定用のHTMLを取得
	 */
	function config(){

	}

	function getCart() {
		return $this->cart;
	}
	function setCart($cart) {
		$this->cart = $cart;
	}

	function getOrder(){
		return $this->order;
	}
	function setOrder($order){
		$this->order = $order;
	}
}
class SOYShopDeliveryDeletageAction implements SOY2PluginDelegateAction{

	private $_list = array();
	private $_method = true;
	private $mode = "list";
	private $cart;
	private $order;
	private $_changes = array();
	private $_config;	//HTML
	private $moduleId;

	function run($extetensionId,$moduleId,SOY2PluginAction $action){

		//カートは必要　マイページでも使用できるようにするため、Cartのチェックはいらない
		// if(!$this->getCart()){
		// 	throw new Exception("soyshop.delivery needs cart information.");
		// }

		if(!is_null($this->getCart())) $action->setCart($this->getCart());
		if(!is_null($this->getOrder())) $action->setOrder($this->getOrder());

		switch($this->mode){
			case "list":
				if(strlen($action->getName())){
					$this->_list[$moduleId] = array(
						"name" => $action->getName(),
						"description" => $action->getDescription(),
						"price" => $action->getPrice()
					);
				}
				break;
			case "method":	//支払い方法のリストの表示のルールを決める
				$this->_method = $action->getMethod($this->getCart(), $this->getModuleId());
				break;
			case "select":
				//念の為、ここでも再度調べる
				if($_POST["delivery_module"] === $moduleId){
					$action->onSelect($this->getCart());
				}
				break;
			case "mypage":
				if(strlen($action->getName())){
					$this->_list[$moduleId] = $action->edit();
				}
				break;
			case "update":
				$this->_changes[$moduleId] = $action->update();
				break;
			//管理画面の注文編集で設定用の項目を追加
			case "config":
				$this->_config = $action->config();
				break;
		}
	}


	function getList(){
		return $this->_list;
	}
	function getMethod(){
		return $this->_method;
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
	function getOrder(){
		return $this->order;
	}
	function setOrder($order){
		$this->order = $order;
	}

	function getChanges(){
		return $this->_changes;
	}

	function getConfig(){
		return $this->_config;
	}

	function getModuleId(){
		return $this->moduleId;
	}
	function setModuleId($moduleId){
		$this->moduleId = $moduleId;
	}
}
SOYShopPlugin::registerExtension("soyshop.delivery","SOYShopDeliveryDeletageAction");

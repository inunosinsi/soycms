<?php
class SOYShopPayment implements SOY2PluginAction{

	private $cart;

	/**
	 * Cartで支払い方法選択後にCartLogicに支払い方法を登録する等の処理を行う
	 */
	function onSelect(CartLogic $cart){
		/**
		 * 支払い方法を登録する
		 * $module = new SOYShop_ItemModule();
		 * $module->setId("payment_*****");       //プラグインIDを指定する
		 * $module->setType("payment_module");    //他の支払いモジュールを登録しないように必ず登録
		 * $module->setName($this->getName());    //支払い方法
		 * $module->setIsInclude(false);          //注文の合算に含めるか？内税の場合はfalseにする
		 * $module->setIsVisible(false);          //顧客に見せるか？
		 * $module->setPrice($this->getPrice());  //当モジュールを選択した時の加算する金額
		 * $cart->addModule($module);
		**/
	}

	/**
	 * @return string
	 * Cartの支払い方法選択画面等で表示する支払い方法名
	 */
	function getName(){
		return "";
	}

	/**
	 * @return string
	 * Cartの支払い方法選択画面で表示する支払い方法の説明文
	 */
	function getDescription(){
		return "";
	}

	/**
	 * @return integer
	 * Cart等で支払い方法として選択した時に加算される金額
	 */
	function getPrice(){
		return 0;
	}

	/**
	 * @return boolen
	 * Cartの支払い方法選択画面で選択項目として表示するか？
	 */
	function getMethod(CartLogic $cart, string $moduleId){
		return true;
	}

	/**
	 * @return boolean
	 * クレジットカードのカード番号の入力ページ等の追加ページを持っているか？
	 */
	function hasOptionPage(){
		return false;
	}

	/**
	 * @return string
	 * hasOptionPageがtrueの場合、注文完了後の追加ページの表示内容
	 */
	function getOptionPage(){
		return "";
	}

	/**
	 * 追加ページでPOSTを送信した後に読み込まれる
	 */
	function onPostOptionPage(){

	}

	function getCart() {
		return $this->cart;
	}
	function setCart($cart) {
		$this->cart = $cart;
	}
}
class SOYShopPaymentDeletageAction implements SOY2PluginDelegateAction{

	private $_list = array();
	private $_method = true;
	private $_hasOption = false;
	private $mode = "list";
	private $cart;
	private $moduleId;

	function run($extentionId,$moduleId,SOY2PluginAction $action){

		//カートは必要
		if(!$this->getCart()){
			throw new Exception("soyshop.payment needs cart information.");
		}

		$action->setCart($this->getCart());

		//optionの時
		if($extentionId == "soyshop.payment.option"){
			if($this->mode == "post"){
				$action->onPostOptionPage();
			}else{
				//モジュールの読み込みの二段階チェック
				if(isset($this->moduleId) && $this->moduleId == $moduleId){
					echo $action->getOptionPage();
				}
			}
			return;
		}

		$this->getCart()->clearAttribute("has_option");

		switch($this->mode){
			case "list"://支払い方法のリスト
				if(strlen((string)$action->getName())){
					$this->_list[$moduleId] = array(
						"name" => $action->getName(),
						"price" => $action->getPrice(),
						"description" => $action->getDescription(),
					);
				}
				break;
			case "method":	//支払い方法のリストの表示のルールを決める
				$this->_method = (is_string($this->getModuleId())) ? $action->getMethod($this->getCart(), $this->getModuleId()) : false;
				break;
			case "select"://選択された支払いの内部
				//念の為、ここでも再度調べる
				if($_POST["payment_module"] === $moduleId){
					$action->onSelect($this->getCart());

					if($action->hasOptionPage()){
						$this->getCart()->setAttribute("has_option", true);

						//環境によってセッションが引き継がれないことがあるから、パラメータに入れておく
						$this->_hasOption = true;
					}
				}
				break;
			case "search":	//管理画面の検索フォーム
				if(strlen($action->getName())){
					$this->_list[$moduleId] = $action->getName();
				}
				break;
			case "mypage":
				if(strlen($action->getName()) && !$action->hasOptionPage()){
					$this->_list[$moduleId] = array(
						"name" => $action->getName(),
						"price" => $action->getPrice(),
						"description" => $action->getDescription(),
					);
				}
				break;
		}
	}


	function getList(){
		return $this->_list;
	}
	function getMethod(){
		return $this->_method;
	}
	function getHasOption(){
		return $this->_hasOption;
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
	function getModuleId(){
		return $this->moduleId;
	}
	function setModuleId($moduleId){
		$this->moduleId = $moduleId;
	}
}
SOYShopPlugin::registerExtension("soyshop.payment","SOYShopPaymentDeletageAction");
SOYShopPlugin::registerExtension("soyshop.payment.option","SOYShopPaymentDeletageAction");

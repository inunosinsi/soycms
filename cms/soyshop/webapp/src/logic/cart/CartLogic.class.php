<?php
SOY2::import("domain.order.SOYShop_ItemOrder");
SOY2::import("domain.order.SOYShop_ItemModule");
SOY2::import("domain.user.SOYShop_User");
SOY2::import("domain.config.SOYShop_ShopConfig");

/**
 * カート全般
 *
 * セッションを使ってカートを保存
 */
class CartLogic extends SOY2LogicBase{

	protected $id;
	protected $items = array();	//商品情報
	protected $customerInformation;//顧客情報
	protected $order;//仮登録後、注文情報が入る

	/**
	 * CartLogic->attributesとCartLogic->orderAttributesについて
	 *
	 * CartLogic->attributes
	 * カートの動作のために一時的に保存する場所。永続化（DB保存）はされない。
	 *
	 * CartLogic->orderAttributes
	 * SOYShop_Order->attributesに変換され永続化される。
	 * 確認画面で表示される。非表示の設定は現状できないができるべき。
	 * clearOrderAttribute("aaa")は"aaa"だけではなく"aaa.*"も消去される
	 *
	 * CartLogic->modules
	 * SOYShop_Order->modulesにそのまま永続化される。
	 * 確認画面で商品一覧の下に表示される。（非表示の設定も可能）
	 * 値はSOYShop_ItemModuleを使う。値で検索することや並び替えることができる。
	 * SOYShop_ItemModuleは金額の設定が可能。送料、代引き手数料、値引き、クーポンなどに使う。
	 * 同じtypeのSOYShop_ItemModuleを設定できないという特徴がある。
	 *
	 */
	protected $modules = array();
	protected $attributes = array();
	protected $orderAttributes = array();

	protected $errorMessage = array();
	protected $noticeMessage = array();

	protected $db;	//CartLogic内の値を格納するためのdbファイル名

	/**
	 * construct
	 */
	function __construct($cartId = null){
		$this->id = $cartId;
		if(!defined("SOYSHOP_USE_CART_TABLE_MODE")) define("SOYSHOP_USE_CART_TABLE_MODE", false);
	}

	/**
	 * カートを取得
	 */
	public static function getCart($cartId = null){
		if(is_null($cartId)) $cartId = (defined("SOYSHOP_CURRENT_CART_ID")) ? SOYSHOP_CURRENT_CART_ID : soyshop_get_cart_id();
		$cart = SOY2ActionSession::getUserSession()->getAttribute("soyshop_" . SOYSHOP_ID . $cartId);
		if(is_string($cart) && strlen($cart)) $cart = soy2_unserialize($cart);
		return ($cart instanceof CartLogic) ? $cart : new CartLogic($cartId);
	}

	/**
	 * カートを保存
	 */
	public static function saveCart(CartLogic $cart){
		SOY2ActionSession::getUserSession()->setAttribute("soyshop_" . SOYSHOP_ID . $cart->getId(), soy2_serialize($cart));
	}

	function save(){
		CartLogic::saveCart($this);
	}

	/**
	 * カートを削除
	 */
	public static function clearCart($cartId = null){
		if(is_null($cartId)) $cartId = soyshop_get_cart_id();
		SOY2ActionSession::getUserSession()->setAttribute("soyshop_" . SOYSHOP_ID . $cartId, null);
	}
	function clear(){
		CartLogic::clearCart($this->getId());
	}

	/**
	 * カートに商品を追加 replaceIdxに値がある場合は商品の差し替え
	 */
	function addItem($itemId, $count = 1, $replaceIdx=null){
		$item = soyshop_get_item_object($itemId);

		//追加不可能
		if(is_null($item->getId()) || false == $item->isOrderable()){
			throw new Exception("Can not orderable");
		}

		//個数は-1以上の整数
		$count = max(-1, (int)$count);
		$items = self::getItems();

		//商品の差し替え
		if(is_numeric($replaceIdx) && isset($items[$replaceIdx])){
			$items[$replaceIdx] = $this->setItemOrder($item, $count);
			self::setItems($items);
			return true;
		}

		//在庫以上は入らない
		//$count = min($item->getOpenStock(),$count);

		//商品オプションの値がポストされている場合。商品オプションプラグインに対応するため、管理画面での挙動を追加する
		$resOpts = (isset($_REQUEST["item_option"]) && is_array($_REQUEST["item_option"])) ? $_REQUEST["item_option"] : array();
		if(!count($resOpts) && (defined("SOYSHOP_ADMIN_PAGE") && SOYSHOP_ADMIN_PAGE)){
			//@ToDo soyshop.item.option拡張ポイントを利用しているものであればすべて対象にしたい
			SOY2::import("util.SOYShopPluginUtil");
			if(SOYShopPluginUtil::checkIsActive("common_item_option")) $resOpts = array("dummy" => null);
		}

		if(count($resOpts)){

			//商品オプションの配列を比較する
			$result = null;

			//すでにカートの中に商品が入っていないかチェック。商品差替モードの場合はカート内に既に同じ商品があっても別商品として扱う
			$res = (is_null($replaceIdx)) ? is_numeric(self::_checkExistedIndex($items, $itemId)) : false;

			//商品があればオプションも同じかどうかを調べる
			if($res){
				//すでにある商品と配列が一致したらtrueを返す
				SOYShopPlugin::load("soyshop.item.option");
				$result = SOYShopPlugin::invoke("soyshop.item.option", array(
					"mode" => "compare",
					"cart" => $this->getCart(),
					"option" => $resOpts
				))->getCartOrderId();

				if(!isset($items[$result]) || $items[$result]->getItemId() != $itemId) $result = null;
			}

			//配列が一致しなかった場合は新しい商品として追加
			if(is_null($result)){
				$items[] = $this->setItemOrder($item, $count);
				self::setItems($items);
				return true;
			}else{
				self::updateItem($result, $count + $items[$result]->getItemCount());
				return false;
			}

		//商品オプションの値がポストされていない場合
		}else{
			//商品差替モードの場合はカート内に既に同じ商品があっても別商品として扱う
			$index = (is_null($replaceIdx)) ? self::_checkExistedIndex($items, $itemId) : null;

			if(isset($index)){
				self::updateItem($index, $count + $items[$index]->getItemCount());
			}else{
				//1個以上ならカートに入れる
				if($count > 0){
					$items[] = $this->setItemOrder($item, $count);
					self::setItems($items);
				}
			}

			//商品オプションがないから必ずfalseを返す
		}

		return false;
	}

	//カートに同じ商品があるか？
	private function _checkExistedIndex($itemOrders, $itemId){
		if(!count($itemOrders)) return null;

		foreach($itemOrders as $index => $itemOrder){
			if((int)$itemOrder->getItemId() === (int)$itemId){
				return $index;
			}
		}
		return null;
	}

	function countItems(){
		$items = self::getItems();
		if(!count($items)) return 0;

		$count = 0;
		foreach($items as $item){
			$count += (int)$item->getItemCount();
		}
		return $count;
	}

	function setItemOrder(SOYShop_Item $item, $count){
		SOYShopPlugin::load("soyshop.cart.set.itemorder");
		$obj = SOYShopPlugin::invoke("soyshop.cart.set.itemorder", array(
			"item" => $item,
			"count" => $count
		))->getObject();

		if(!is_null($obj) && $obj instanceof SOYShop_ItemOrder){
			return $obj;
		}

		$obj = new SOYShop_ItemOrder();
		$obj->setItemId($item->getId());
		$obj->setItemCount($count);
		$obj->setItemPrice($item->getSellingPrice());
		$obj->setTotalPrice($item->getSellingPrice() * $count);
		$obj->setItemName($item->getName());

		return $obj;
	}

	/**
	 * カートから商品を削除
	 */
	function removeItem($index){
		$items = self::getItems();
		if(isset($items[$index])){
			$items[$index] = null;
			unset($items[$index]);
			self::setItems($items);
		}
	}

	/**
	 * カートでアイテム数の個数を更新
	 */
	function updateItem($index, $count){
		if($count === 0){
			$isRemove = true;
			//管理画面で注文の場合はカートに0個を許可する設定がある
			if(defined("SOYSHOP_ADMIN_PAGE") && SOYSHOP_ADMIN_PAGE){
				SOY2::import("domain.config.SOYShop_ShopConfig");
				$cnf = SOYShop_ShopConfig::load();
				if($cnf->getAllowRegistrationZeroQuantityProducts()) $isRemove = false;
			}

			if($isRemove){
				self::removeItem($index);
				return;
			}

		}

		$items = self::getItems();
		if(isset($items[$index])){
			$items[$index]->setItemCount($count);
			$items[$index]->setTotalPrice($items[$index]->getItemPrice() * $count);

			SOYShopPlugin::load("soyshop.item.order");
			SOYShopPlugin::invoke("soyshop.item.order", array(
				"mode" => "update",
				"itemOrder" => $items[$index]
			));

			self::setItems($items);
		}
	}

	/**
	 * 商品合計金額を取得
	 * @return number
	 */
	function getItemPrice(){
		if(SOYSHOP_USE_CART_TABLE_MODE){
			return soyshop_cart_get_item_price($this->db);
		}else{
			$items = self::getItems();
			if(!count($items)) return 0;

			$total = 0;
			foreach($items as $item){
				$total += $item->getTotalPrice();
			}

			return $total;
		}
	}

	/**
	 * @return integer 商品数を取得
	 */
	// function getItemCount(){
	// 	return count(self::getItems());
	// }

	/**
	 * @return integer 商品の個数の合計
	 */
	function getOrderItemCount(){
		if(SOYSHOP_USE_CART_TABLE_MODE){
			return soyshop_cart_get_item_count($this->db);
		}else{
			$items = self::getItems();
			if(!count($items)) return 0;

			$total = 0;
			foreach($items as $item){
				$total += $item->getItemCount();
			}

			return $total;
		}
	}

	/**
	 * モジュール追加
	 * 同じタイプのモジュールは１つしか追加できない
	 */
	function addModule(SOYShop_ItemModule $module){
		$id = $module->getId();

		//同一タイプは削除する
		if(strlen($module->getType()) > 0){
			foreach($this->modules as $key => $value){
				if($value->getType() == $module->getType()){
					$this->removeModule($key);
				}
			}
		}

		$this->modules[$id] = $module;
	}

	/**
	 * モジュール削除
	 */
	function removeModule($moduleId){
		if(isset($this->modules[$moduleId])){
			$this->modules[$moduleId] = null;
			unset($this->modules[$moduleId]);
		}

		//子モジュールを削除
		foreach($this->modules as $id => $module){
			if(preg_match("/^$moduleId\..+/", $id)){
				unset($this->modules[$id]);
			}
		}

		//関連する設定値をクリア
		$this->clearOrderAttribute($moduleId);
	}

	/**
	 * モジュール取得
	 */
	function getModule($moduleId){
		return (isset($this->modules[$moduleId])) ? $this->modules[$moduleId] : null;
	}

	function calculateConsumptionTax(){
		if(defined("SOYSHOP_CONSUMPTION_TAX_MODE") && SOYSHOP_CONSUMPTION_TAX_MODE){
			//外税(プラグインによる処理)
			$this->setConsumptionTax();
		}elseif(defined("SOYSHOP_CONSUMPTION_TAX_INCLUSIVE_PRICING_MODE") && SOYSHOP_CONSUMPTION_TAX_INCLUSIVE_PRICING_MODE){
			//内税(標準実装)
			$this->setConsumptionTaxInclusivePricing();
		}else{
			//何もしない
		}
	}

	/**
	 * 消費税をセットする
	 * typeがtaxのプラグインで処理を行う
	 */
	function setConsumptionTax(){
		$config = SOYShop_ShopConfig::load();
		$pluginId = $config->getConsumptionTaxModule();

		if(!isset($pluginId)) return false;

		$plugin = soyshop_get_plugin_object($pluginId);
   		if(is_null($plugin->getId()) || $plugin->getIsActive() == SOYShop_PluginConfig::PLUGIN_INACTIVE) return false;

   		SOYShopPlugin::load("soyshop.tax.calculation", $plugin);
		SOYShopPlugin::invoke("soyshop.tax.calculation", array(
			"mode" => "post",
			"cart" => $this
		));
	}

	/**
	 * 消費税の内税をセットする。内税表示モード
	 */
	function setConsumptionTaxInclusivePricing(){
		$this->removeModule("consumption_tax");

		$items = self::getItems();
		if(count($items) === 0) return;

		$totalPrice = 0;

		foreach($items as $item){
			$totalPrice += $item->getTotalPrice();
		}

		if($totalPrice === 0) return;

		foreach($this->getModules() as $mod){
			//値引き分も加味するので、isIncludeされていない値は0以上でなくても加算対象
			if(!$mod->getIsInclude()){
				$totalPrice += (int)$mod->getPrice();
			}
		}

		$config = SOYShop_ShopConfig::load();
		$taxRate = (int)$config->getConsumptionTaxInclusivePricingRate();	//内税率

		if($taxRate === 0) return;

		$module = new SOYShop_ItemModule();
		$module->setId("consumption_tax");
		$module->setName("内税");
		$module->setType(SOYShop_ItemModule::TYPE_TAX);	//typeを指定しておくといいことがある
		//内税の計算は8%の場合はtax = total / 1.08で計算する
		$module->setPrice(floor($totalPrice - ($totalPrice / (1 + $taxRate / 100))));
		$module->setIsInclude(true);	//合計に合算されない
		$this->addModule($module);
	}

	//taxモジュールが登録されているか？をチェックする
	function checkTaxModule(){
		$modules = $this->getModules();

		if(count($modules) === 0) return false;

		$res = false;
		foreach($modules as $module){
			if($module->getType() == SOYShop_ItemModule::TYPE_TAX){
				$res = true;
				break;
			}
		}

		return $res;
	}

	/**
	 * 総合計金額を取得
	 * @return number
	 */
	function getTotalPrice($exceptedTax = false){
		$total = $this->getItemPrice();

		foreach($this->modules as $moduleId => $module){
			//外税を省いた合算モード
			if($exceptedTax && $moduleId == "consumption_tax") continue;

			//明細に記載されるのみモジュールに追加
			if($module->isInclude()) continue;

			$total += $module->getPrice();
		}

		return $total;
	}

	/**
	 * カートに入れている商品が更新されていないかチェック
	 *
	 * @return boolean
	 */
	function checkUpdated(){

	}

	function getAttributes() {
		return $this->attributes;
	}
	function setAttributes($attributes) {
		$this->attributes = $attributes;
	}

	function setAttribute($id, $value){
		$this->attributes[$id] = $value;
		$this->save();
	}
	function getAttribute($id){
		return (isset($this->attributes[$id])) ? $this->attributes[$id] : null;
	}
	function clearAttribute($id){
		$this->attributes[$id] = null;
		unset($this->attributes[$id]);
	}

	function getOrderAttributes() {
		return $this->orderAttributes;
	}
	function setOrderAttributes($orderAttributes) {
		$this->orderAttributes = $orderAttributes;
	}

	function setOrderAttribute($id, $name, $value, $hidden = false, $readonly = false){
		if(!is_array($this->orderAttributes)) $this->orderAttributes = array();
		$this->orderAttributes[$id] = array(
			"name" => $name,
			"value" => $value,
			"hidden" => $hidden,
			"readonly" => $readonly,
		);
		$this->save();
	}
	function getOrderAttribute($id){
		return (isset($this->orderAttributes[$id])) ? $this->orderAttributes[$id] : null;
	}
	function clearOrderAttribute($id){
		if(isset($this->orderAttributes[$id])){
			$this->orderAttributes[$id] = null;
			unset($this->orderAttributes[$id]);
		}

		//関連のattributeを削除
		foreach($this->orderAttributes as $key => $attr){
			if(strpos($key, $id.".") === 0){
				unset($this->orderAttributes[$key]);
			}
		}
	}

	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}
	function getItems() {
		return (SOYSHOP_USE_CART_TABLE_MODE) ? soyshop_cart_get_items($this->db) : $this->items;
	}
	function setItems($items) {
		if(SOYSHOP_USE_CART_TABLE_MODE){
			// @ToDo データベースインサートモード
			$this->db = soyshop_cart_set_items($this->db, $items);
		}else{
			$this->items = $items;
		}
	}
	function getOrder(){
		return $this->order;
	}
	function setOrder($order){
		$this->order = $order;
	}
	function getCustomerInformation() {
		if(is_null($this->customerInformation)){
			//マイページのログインも試す 管理画面から注文の場合は試さない
			if(!defined("SOYSHOP_ADMIN_PAGE") || !SOYSHOP_ADMIN_PAGE){
				if(class_exists("MyPageLogic")){
					$mypage = MyPageLogic::getMyPage();
					if($mypage->getIsLoggedin()){
						$this->customerInformation = $mypage->getUser();
						$this->save();
					}
				}
			}

			if(is_null($this->customerInformation)) $this->customerInformation = new SOYShop_User();
	   	}
		return $this->customerInformation;
	}
	function setCustomerInformation($customerInformation) {
		$this->customerInformation = $customerInformation;
	}

	/**
	 * 商品送付先を取得
	 */
	function getAddress(){
		$key = $this->getAttribute("address_key");
		if(is_null($key)) $key = -1;
		return $this->customerInformation->getAddress($key);
	}

	function getClaimedAddress(SOYShop_User $user){
		//$user = $this->customerInformation;
		return array(
			"name" => $user->getName(),
			"reading" => $user->getReading(),
			"zipCode" => $user->getZipCode(),
			"area" => $user->getArea(),
			"address1" => $user->getAddress1(),
			"address2" => $user->getAddress2(),
			"address3" => $user->getAddress3(),
			"telephoneNumber" => $user->getTelephoneNumber(),
			"office" => $user->getJobName(),
		);
	}

	/**
	 * 宛先が指定されているかどうか
	 * @return boolean
	 */
	function isUseCutomerAddress(){
		$key = $this->getAttribute("address_key");
		if(is_null($key)) $key = -1;
		return ($key >= 0);
	}


	function getModules() {
		return $this->modules;
	}
	function setModules($modules) {
		$this->modules = $modules;
	}

	/**
	 * @param all boolean
	 * trueの場合はすべてのモジュールをクリアする
	 * falseの場合はisVisibleがtrueのもののみクリアする
	 */
	function clearModules($all = false){
		foreach($this->modules as $moduleId => $module){
			if($all === false && $module->getIsVisible() === false) continue;
			$this->removeModule($moduleId);
		}
	}

	/*
	 * 以下注文を実行したりなんだり。
	 */

	/**
	 * 注文実行
	 */
	function order(){

		//配送ダミーか支払いダミーモジュールがインストールされている場合、ダミーモジュールでセットを試みる
		//これで配送と支払いのどちらもダミーのサイトを構築することができる
		if(!count($this->getModules())){
			self::setDummyModule();
		}

		//念の為に登録したモジュールが消えてないか調べて、消えていればCart03に飛ばす
		//有効な注文にはモジュールかOrderAttributeかどちらかが最低１つ必要
		if(!count($this->getModules()) || !count($this->getOrderAttributes())){

			$this->setAttribute("page", "Cart03");
			$this->save();
			$this->log("No module and no order_attribute. At least one module or one order_attribute should be specified.");

			soyshop_redirect_cart();
		}

		//ユーザーエージェント
		if(isset($_SERVER['HTTP_USER_AGENT'])){
			$this->setOrderAttribute("order_check_carrier", "ユーザーエージェント", $_SERVER['HTTP_USER_AGENT'], true, true);
		}

		//IPアドレス
		if(isset($_SERVER['REMOTE_ADDR'])){
			$this->setOrderAttribute("order_ip_address", "IPアドレス", $_SERVER['REMOTE_ADDR'], true, true);
		}

		//$_SERVER
		//$this->setOrderAttribute("order_server", "\$_SERVER", var_export($_SERVER,true), true, true);

		//初回購入であるか調べる
		if($this->checkFirstOrder()){
			$this->setOrderAttribute("order_first_order", "初回購入", "初回購入", true, true);
		}

		//setOrderAttributeに追加出来る拡張ポイント
		SOYShopPlugin::load("soyshop.order.complete");
		SOYShopPlugin::invoke("soyshop.order.complete", array(
			"mode" => "before",
			"cart" => $this,
		));

		$orderDAO = SOY2DAOFactory::create("order.SOYShop_OrderDAO");

		try{
			//transaction start
			$orderDAO->begin();

			//注文可能かチェック
			$this->checkOrderable();

			//注文カスタムフィールド　顧客情報の変更を必要とするものもあるため、先に実行しておく
			SOYShopPlugin::load("soyshop.order.customfield");
			SOYShopPlugin::invoke("soyshop.order.customfield", array(
				"mode" => "order",
				"cart" => $this,
			));

			//顧客情報の登録
			$this->registerCustomerInformation();

			//ユーザカスタムフィールド　顧客IDが決まってからでないと実行できないものがあるため、顧客登録の後に実行
			SOYShopPlugin::load("soyshop.user.customfield");
			SOYShopPlugin::invoke("soyshop.user.customfield",array(
				"mode" => "register",
				"app" => $this,
				"userId" => $this->getCustomerInformation()->getId()
			));

			//注文情報の登録
			$this->orderItems();

			//もう一度カスタムフィールドを実行する
			SOYShopPlugin::invoke("soyshop.order.customfield", array(
				"mode" => "complete",
				"cart" => $this,
			));

			//記録
			$orderLogic = SOY2Logic::createInstance("logic.order.OrderLogic");
			$orderLogic->addHistory($this->getAttribute("order_id"), "注文を受け付けました");

			$orderDAO->commit();
		}catch(SOYShop_EmptyStockException $e){
			$this->log($e);
			throw $e;
		}catch(SOYShop_OverStockException $e){
			$this->log($e);
			throw $e;
		}catch(Exception $e){
			$this->log("---------- Exception in CartLogic->order() ----------");
			$this->log($e);
			$this->log(var_export($this,true));
			$this->log(var_export($e,true));
			$this->log("---------- /Exception ----------");
			$orderDAO->rollback();
			throw new Exception("注文実行時にエラーが発生しました。");
		}
	}

	private function setDummyModule(){
		if(SOYShopPluginUtil::checkIsActive("payment_admin_dummy")){
			$module = new SOYShop_ItemModule();
			$module->setId("payment_admin_dummy");
			$module->setType("payment_module");//typeを指定しておくといいことがある
			$module->setName("決済手数料ダミー");
			$module->setIsVisible(false);
			$module->setIsInclude(false);
			$this->addModule($module);
		}else if(SOYShopPluginUtil::checkIsActive("delivery_admin_dummy")){
			$module = new SOYShop_ItemModule();
			$module->setId("delivery_admin_dummy");
			$module->setName("送料ダミー");
			$module->setType("delivery_module");
			$module->setIsVisible(false);
			$module->setIsInclude(false);
			$this->addModule($module);
		}

		//何もしない
	}

	/**
	 * 注文完了（メール送信あり）
	 * @return boolean
	 */
	function orderComplete(){
		return $this->_orderComplete(true);
	}

	/**
	 * 注文完了（メール送信なし）
	 * @return boolean
	 */
	function orderCompleteWithoutMail(){
		return $this->_orderComplete(false);
	}

	/**
	 * 注文完了
	 *
	 * ・フラグを仮登録->登録に変更
	 * ・メールを送信
	 *
	 * @param boolean $sendMail メール送信可否
	 * @return boolean
	 */
	function _orderComplete($sendMail = true){

		$orderLogic = SOY2Logic::createInstance("logic.order.OrderLogic");
		try{
			$order = soyshop_get_order_object($this->getAttribute("order_id"));
		}catch(Exception $e){
			$orderLogic->addHistory($this->getAttribute("order_id"), "注文を完了することができませんでした。メールは送信されません。");
			return false;
		}

		//既に完了していた場合はtrueを返す
		if($order->getStatus() == SOYShop_Order::ORDER_STATUS_REGISTERED){
			return true;
		}else{
			$order->setStatus(SOYShop_Order::ORDER_STATUS_REGISTERED);
			try{
				SOY2DAOFactory::create("order.SOYShop_OrderDAO")->updateStatus($order);
			}catch(Exception $e){
				$orderLogic->addHistory($this->getAttribute("order_id"), "注文を完了することができませんでした。メールは送信されません。");
				return false;
			}
		}

		//注文確定時に関するプラグインを実行する
		SOYShopPlugin::load("soyshop.order.complete");
		SOYShopPlugin::invoke("soyshop.order.complete", array(
			"order" => $order
		));

		//準備
		$this->order = $order;

		//メールの送信
		if($sendMail){
			try{
				$this->sendMail();
			}catch(Exception $e){
				//メール送信に失敗した場合
				$orderLogic->addHistory($this->getAttribute("order_id"), "注文受付メールの送信に失敗しました。");
			}
		}else{
			$orderLogic->addHistory($this->getAttribute("order_id"), "注文受付メールの送信はスキップされました。");
		}

		//CartLogicの内容の一部をSQLite DBに移行するモードの場合はデータベースを削除する
		if(SOYSHOP_USE_CART_TABLE_MODE){
			soyshop_cart_delete_db($this->db);
			soyshop_cart_routine_delete_db();
		}

		return true;
	}

	/**
	 * 支払確認
	 *
	 * ・支払い状況を支払待ちから支払確認済みに変更
	 * ・メールを送信
	 *
	 * @param $orderId
	 * @return boolean
	 */
	function orderPaymentConfirm(){

		$orderLogic = SOY2Logic::createInstance("logic.order.OrderLogic");

		try{
			$order = soyshop_get_order_object($this->getAttribute("order_id"));

			//既に完了していた場合はtrueを返す
			if($order->getPaymentStatus() == SOYShop_Order::PAYMENT_STATUS_CONFIRMED){
				return true;
			}else{
				$order->setPaymentStatus(SOYShop_Order::PAYMENT_STATUS_CONFIRMED);

				//仮登録時に支払確認状態になった場合、注文状態を新規受付に変更する
				if($order->getStatus() == SOYShop_Order::ORDER_STATUS_INTERIM){
					$order->setStatus(SOYShop_Order::ORDER_STATUS_REGISTERED);
					$orderLogic->addHistory($this->getAttribute("order_id"), "支払を確認したので、注文状態を仮登録から新規受付に変更しました。");
				}

				SOY2DAOFactory::create("order.SOYShop_OrderDAO")->updateStatus($order);
				$orderLogic->addHistory($this->getAttribute("order_id"), "支払いを確認しました。");
			}
		}catch(Exception $e){
			$orderLogic->addHistory($this->getAttribute("order_id"), "支払いを確認できませんでした。メールは送信されません。");
			return false;
		}

		//準備
		$this->order = $order;

		//支払確認済みに関するプラグインを実行する
		SOYShopPlugin::load("soyshop.order.status.update");
		SOYShopPlugin::invoke("soyshop.order.status.update", array(
			"order" => $this->order,
			"mode" => "status"
		));

		try{
			//完了したらメールの送信
			$this->sendMail("payment");

			$orderLogic->setMailStatus($this->getAttribute("order_id"), "payment", time());
		}catch(Exception $e){
			//メール送信に失敗した場合
			$orderLogic->addHistory($this->getAttribute("order_id"), "支払い確認メールの送信に失敗しました。");
		}

		return true;
	}

	/**
	 * 注文可能かチェック
	 * $allがfalseの場合(クレジットカード支払関係)は予約カレンダーの残席チェックは行わない
	 */
	function checkOrderable($all=true){
		$itemDAO = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		$config = SOYShop_ShopConfig::load();
		$ignoreStock = $config->getIgnoreStock();

		//transaction start
		//$itemDAO->begin();

		$items = $this->getItems();

		SOY2::import("util.SOYShopPluginUtil");
		$reserveCalendarMode = SOYShopPluginUtil::checkIsActive("reserve_calendar");
		if($reserveCalendarMode) SOY2::import("module.plugins.reserve_calendar.util.ReserveCalendarUtil");

		//合算　予約で使う
		$itemCountTotalList = array();
		foreach($items as $index => $itemOrder){
			$itemId = $itemOrder->getItemId();

			//管理画面から購入時の未登録商品の購入
			if($itemId < 1) continue;

			$item = soyshop_get_item_object($itemId);

			//非公開
			if(false == $item->isPublished()){
				throw new SOYShop_EmptyStockException($item->getName()." (".$item->getId().") is not published.");
			}

			//カートに入れた商品
			$itemCount = $itemOrder->getItemCount();

			if(!$reserveCalendarMode){	//通常モード
				//在庫無視モード
				if($ignoreStock) continue;

				//在庫0
				if($item->getOpenStock() < 1){
					throw new SOYShop_EmptyStockException($item->getName()." (".$item->getId().") is empty (stock is 0).");
				}

				$openStock = $item->getOpenStock();

				//子商品の在庫管理設定をオン(子商品購入時に親商品の在庫数で購入できるか判断する)
				$childItemStock = $config->getChildItemStock();
				if($childItemStock && is_numeric($item->getType())){
					//親商品の残り在庫数を取得
					$parent = soyshop_get_item_object($item->getType());
					$openStock = $parent->getStock();

					//子商品の注文数の合計を取得
					$itemCount = $this->getChildItemOrders($parent->getId());
				}

				//在庫オーバー
				if($openStock < $itemCount){
					throw new SOYShop_OverStockException($item->getName()." (".$item->getId().") is fewer (" . $openStock . ") than order (" . $itemCount . ").");
				}
			}else{	//予約カレンダーモード $allがfalseの場合は調べない
				if($all){
					$schedule = ReserveCalendarUtil::getScheduleByItemIndexAndItemId($this, $index, $itemOrder->getItemId());

					//予約可のスケジュールがなくなった
					if(is_null($schedule->getId())){
						throw new SOYShop_EmptyStockException($item->getName()." (".$item->getId().") is none.");
					}

					//定員数0
					if(!ReserveCalendarUtil::checkIsUnsoldSeatByScheduleId($schedule->getId())){
						throw new SOYShop_EmptyStockException($item->getName()." (".$item->getId().") is empty (stock is 0).");
					}

					//定員数オーバー @ToDo 仮登録を含めるか？
					$unsoldSeat = ReserveCalendarUtil::getCountUnsoldSeat($schedule);
					if(!isset($itemCountTotalList[$schedule->getId()])) $itemCountTotalList[$schedule->getId()] = 0;
					$itemCountTotalList[$schedule->getId()] += $itemCount;
					if($unsoldSeat < $itemCountTotalList[$schedule->getId()]){
						throw new SOYShop_OverStockException($item->getName()." (".$item->getId().") is fewer (" . $unsoldSeat . ") than order (" . $itemCountTotalList[$schedule->getId()] . ").");
					}
				}
			}

			//販売期間
			if($item->getOrderPeriodStart() > time() || $item->getOrderPeriodEnd() < time()){
				throw new SOYShop_AcceptOrderException("");
			}
		}
	}

	function checkItemCountInCart(){
		$items = $this->getItems();

		//カートに商品が入っていない
		if(count($items) === 0){
			throw new SOYShop_EmptyCartException("");
		}
	}

	//子商品の注文数の合計
	function getChildItemOrders($itemId){
		$cart = CartLogic::getCart();

		$itemCount = 0;

		$items = $cart->getItems();
		if(count($items) > 0){
			$dao = new SOY2DAO();
			$sql = "select id from soyshop_item where item_type = " . $itemId;
			try{
				$result = $dao->executeQuery($sql);
			}catch(Exception $e){
			}
			$ids = array();
			foreach($result as $value){
				$ids[] = $value["id"];
			}

			foreach($items as $item){
				if(in_array($item->getItemId(), $ids)){
					$itemCount = $itemCount + $item->getItemCount();
				}
			}
		}

		return $itemCount;
	}

	/**
	 * 顧客情報の登録
	 *
	 * ・最初の画面でユーザ名とパスワード入力→ID取得
	 * ・新規登録(パスワード入力)→ID取得
	 * ・新規登録(パスワード未入力、初回)→登録
	 * 		宛先情報などは登録しない
	 * ・新規登録(パスワード未入力、2回目)→ID取得
	 */
	function registerCustomerInformation(){
		$user = $this->getCustomerInformation();
		$userDAO = SOY2DAOFactory::create("user.SOYShop_UserDAO");

		//登録済みユーザーかどうか
		try{
			$tmpUser = $userDAO->getByMailAddress($user->getMailAddress());
		}catch(Exception $e){
			$tmpUser = null;
		}

		//二回目以降のユーザ
		if($tmpUser instanceof SOYShop_User){

			//ログインしていてもuser_idを持っていないことがある。→ soyshop.mypage.loginの拡張機能の影響
			if( $this->getAttribute("logined") && !is_null($this->getAttribute("logined_userid"))){
				$id = $this->getAttribute("logined_userid");
				$newPassword = $this->getAttribute("new_password");

				$user->setId($id);

				if( strlen($newPassword) ){
					//もし新しいパスワードが入力されていたらパスワードを上書きする
					$user->setPassword($user->hashPassword($newPassword));

					//本登録にする
					$user->setRealRegisterDate(time());
					$user->setUserType(SOYShop_User::USERTYPE_REGISTER);
				}else{
					//それ以外はパスワードを残す
					try{
						$user->setPassword($userDAO->getById($id)->getPassword());
					}catch(Exception $e){
						//
					}
				}

				//update
				$userDAO->update($user);

			//ログインしていないのでゲスト注文となる
			}else{
				$id = $tmpUser->getId();

				//既に登録されているメールアドレスは会員で、今回はゲスト注文
				if(strlen($tmpUser->getPassword()) > 0){
					//顧客情報を更新せず

				//ゲスト注文二回目
				}else{
					$user->setId($id);

					$user = clone($user);
					$user->setAddressList($tmpUser->getAddressList());

					//旧ユーザのパスワードが空なら登録する
					if(strlen($tmpUser->getPassword()) < 1 && strlen($user->getPassword()) > 0){
						$user->setPassword($user->hashPassword($user->getPassword()));
					}else{
						$user->setPassword($tmpUser->getPassword());
					}

					//本登録にしておく
					if($user->getUserType() != SOYShop_User::USERTYPE_REGISTER){
						$user->setRealRegisterDate(time());
						$user->setUserType(SOYShop_User::USERTYPE_REGISTER);
					}

					//update
					$userDAO->update($user);
				}
			}

		//初回ユーザ
		}else{
			//パスワード未設定の時は宛先情報を保持しない
			if(strlen($user->getPassword()) < 1){
				$user = clone($user);
				$user->setAddressList(serialize(null));
			}

			//本登録にする
			$user->setRealRegisterDate(time());
			$user->setUserType(SOYShop_User::USERTYPE_REGISTER);

			//insert: パスワードのハッシュ化はonInsertで行う
			$id = $userDAO->insert($user);
		}

		$this->customerInformation->setId($id);
	}

	/**
	 * 初回購入であるか調べる
	 */
	function checkFirstOrder(){
		$userId = $this->getCustomerInformation()->getId();
		if(!isset($userId)) return true;
		try{
			$orders = SOY2DAOFactory::create("order.SOYShop_OrderDAO")->getByUserId($userId);
		}catch(Exception $e){
			$orders = array();
		}

		return (!count($orders));
	}

	/**
	 * 商品情報の登録
	 */
	function orderItems(){
		$itemDAO = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		$orderDAO = SOY2DAOFactory::create("order.SOYShop_OrderDAO");

		$order = new SOYShop_Order();
		$order->setOrderDate(time());
		$order->setPrice($this->getTotalPrice());
		$order->setUserId($this->customerInformation->getId());
		$order->setItems($this->getItems());
		$order->setModules($this->getModules());
		$order->setAttributes($this->getOrderAttributes());

		//注文のStatusは仮登録
		$order->setStatus(SOYShop_Order::ORDER_STATUS_INTERIM);

		//支払状況
		if($order->getPrice() > 0){
			$order->setPaymentStatus(SOYShop_Order::PAYMENT_STATUS_WAIT);
		}else{
			//0円なら支払い済みにする
			$order->setPaymentStatus(SOYShop_Order::PAYMENT_STATUS_CONFIRMED);
		}

		//送信先
		$address = $this->getAddress();
		$order->setAddress(serialize($address));

		$claimedAddress = $this->getClaimedAddress($this->customerInformation);
		$order->setClaimedAddress($claimedAddress);

		$id = $orderDAO->insert($order);
		$order->setId($id);
		$this->setAttribute("order_id", $id);

		$itemOrderDAO = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO");
		$items = $this->getItems();

		//foreach内で読み込む拡張ポイントはここでロードしておく
		SOYShopPlugin::load("soyshop.item.option");
		SOYShopPlugin::load("soyshop.item.order");

		foreach($items as $key => $item){
			$config = SOYShop_ShopConfig::load();

			//子商品かどうかの判定が必要

			//在庫を減らす（管理画面から未登録商品を追加したときはスキップ）
			if($item->getItemId() > 0){
				//子商品の在庫管理設定をオン(子商品購入時に在庫を減らさない)
				$noChildItemStock = $config->getNoChildItemStock();
				if(!$noChildItemStock) $itemDAO->orderItem($item->getItemId(), $item->getItemCount());

				//子商品の在庫管理設定をオン(子商品購入時に親商品の在庫も減らす)
				$childItemStock = $config->getChildItemStock();
				if($childItemStock){
					//SOYShop_Item
					$itemObj = soyshop_get_item_object($item->getItemId());
					if(is_numeric($itemObj->getType())){
						$parent = soyshop_get_item_object($itemObj->getType());
						$itemDAO->orderItem($parent->getId(), $item->getItemCount());
					}
				}
			}

			$item->setOrderId($id);

			//商品オプションがある場合は、attributeに値を挿入
			$attrs = SOYShopPlugin::invoke("soyshop.item.option", array(
				"mode" => "order",
				"index" => $key
			))->getAttributes();
			$item->setAttributes($attrs);

			//加算オプションがある場合は、is_additionに値を挿入
			$add = SOYShopPlugin::invoke("soyshop.item.option", array(
				"mode" => "addition",
				"index" => $key
			))->getAddition();
			$item->setIsAddition($add);

			//どこかで商品名がnullになる場合があるのでその対処
			if(is_null($item->getItemName())) $item->setItemName(soyshop_get_item_object($item->getItemId())->getName());
			$itemOrderId = $itemOrderDAO->insert($item);

			// SOYShop_ItemOrderに関することなら何でもできる

			SOYShopPlugin::invoke("soyshop.item.order", array(
				"mode" => "order",
				"itemOrderId" => $itemOrderId
			));
		}

		$orderLogic = SOY2Logic::createInstance("logic.order.OrderLogic");
		$trackingNumber = $orderLogic->getTrackingNumber($order);
		$order->setTrackingNumber($trackingNumber);
		$orderDAO->update($order);

		SOYShopPlugin::invoke("soyshop.item.order", array(
			"mode" => "complete",
			"orderId" => $order->getId()
		));


		//begin
		//$orderDAO->commit();

		$this->order = $order;

	}

	/**
	 * メールの送信
	 */
	function sendMail($type="order"){

		$logic = SOY2Logic::createInstance("logic.mail.MailLogic");
		$user = $this->getCustomerInformation();
		$orderLogic = SOY2Logic::createInstance("logic.order.OrderLogic");

		SOYShopPlugin::load("soyshop.order.mail");

		/**
		 * ユーザー宛のメール
		 *
		 * ヘッダー（管理画面＞注文受付メール設定）
		 * 注文内容
		 * プラグイン（soyshop.order.mail.user）
		 * フッター（管理画面＞注文受付メール設定）
		 */
		//DBから設定を取得：ヘッダー、フッター
		$userMailConfig = $logic->getUserMailConfig($type);

		if(isset($userMailConfig["active"]) && $userMailConfig["active"]){
			//メール本文（注文内容）を取得
			list($mailBody, $title) = $logic->buildMailBodyAndTitle($this->order, $type, "user");

			//宛名
			$userName = $this->getCustomerInformation()->getName();
			if(strlen($userName) > 0) $userName .= " 様";

			//送信
			$logic->sendMail($this->getCustomerInformation()->getMailAddress(), $title, $mailBody, $userName, $this->order);

			//メール送信フラグ
			$orderLogic->setMailStatus($this->getAttribute("order_id"), "order", time());

			//ログ
			$orderLogic->addHistory($this->getAttribute("order_id"), "注文者宛の".$logic->getMailTypeName($type, false)."を送信しました。");
		}else{
			//ログ
			$orderLogic->addHistory($this->getAttribute("order_id"), "設定により注文者宛の".$logic->getMailTypeName($type, false)."は送信されません。");
		}

		/**
		 * 管理者（メール設定の「送信元」）宛のメール
		 *
		 * ヘッダー（管理画面＞管理者メール設定）
		 * 注文内容
		 * 注文者情報
		 * プラグイン（soyshop.order.mail.admin）
		 * フッター（管理画面＞管理者メール設定）
		 */
		//DBから設定を取得：ヘッダー、フッター
		$adminMailConfig = $logic->getAdminMailConfig($type);

		if(isset($adminMailConfig["active"]) && $adminMailConfig["active"]){
			//メール本文（注文内容）を取得
			list($mailBody, $title) = $logic->buildMailBodyAndTitle($this->order, $type, "admin");

			//送信
			//@TODO 複数管理者へのメール送信
			$serverConfig = SOYShop_ServerConfig::load();
			$adminMailAddress = $serverConfig->getAdministratorMailAddress();
			$adminName = $serverConfig->getAdministratorName();
			$logic->sendMail($adminMailAddress, $title, $mailBody, $adminName, $this->order, true);

			//ログ
			$orderLogic->addHistory($this->getAttribute("order_id"), "管理者宛の".$logic->getMailTypeName($type, true)."を送信しました。");
		}else{
			//ログ
			$orderLogic->addHistory($this->getAttribute("order_id"), "設定により管理者宛の".$logic->getMailTypeName($type, true)."は送信されません。");
		}
	}

	/**
	 * カートのエラー状態に送信する通知メール
	 */
	function sendNoticeCartErrorMail($exception){
		SOY2::import("domain.config.SOYShop_ServerConfig");
		$serverConfig = SOYShop_ServerConfig::load();
		$adminMailAddress = $serverConfig->getAdministratorMailAddress();
		$adminName = $serverConfig->getAdministratorName();
		$title = "[SOY Shop]カート内でエラーが発生しました";
		$body = "エラー内容は下記の通りです。\n\n";
		//パスの隠蔽
		$body .= str_replace(dirname(SOYSHOP_ROOT), "/*************", $exception);
		SOY2Logic::createInstance("logic.mail.MailLogic")->sendMail($adminMailAddress, $title, $body, $adminName);
	}

	/**
	 * エラーメッセージ
	 */
	function addErrorMessage($id, $str){
		if(DEBUG_MODE)$this->log($id." ".$str);
		$this->errorMessage[$id] = $str;
	}

	/**
	 * エラーメッセージのクリア
	 */
	function removeErrorMessage($id){
		if(isset($this->errorMessage[$id])){
			unset($this->errorMessage[$id]);
		}
	}

	/**
	 * 取得
	 */
	function getErrorMessage($id){
		return (isset($this->errorMessage[$id])) ? $this->errorMessage[$id] : null;
	}

	/**
	 * チェック
	 * @return boolean
	 */
	function hasError($id = null){
		if(isset($id) && strlen($id) > 0){
			return isset($this->errorMessage[$id]) && (strlen($this->errorMessage[$id]) > 0);
		}else{
			if(DEBUG_MODE){
				$this->log("number of errors: ".count($this->errorMessage));
				if(count($this->errorMessage)){
					$this->log("errors: ".var_export($this->errorMessage,true));
				}
			}
			return (count($this->errorMessage) > 0);
		}
	}

	/**
	 * 全て
	 */
	function getErrorMessages(){
		return $this->errorMessage;
	}

	/**
	 *
	 */
	function clearErrorMessage(){
		$this->errorMessage = array();
	}

	/**
	 * 通知メッセージ
	 */
	function addNoticeMessage($id, $str){
		$this->noticeMessage[$id] = $str;
	}

	/**
	 * 通知のクリア
	 */
	function removeNoticeMessage($id){
		if(isset($this->noticeMessage[$id])){
			unset($this->noticeMessage[$id]);
		}
	}

	/**
	 * 取得
	 */
	function getNoticeMessage($id){
		return (isset($this->noticeMessage[$id])) ? $this->noticeMessage[$id] : null;
	}

	/**
	 * 全て
	 */
	function getNoticeMessages(){
		return $this->noticeMessage;
	}

	/**
	 *
	 */
	function clearNoticeMessage(){
		$this->noticeMessage = array();
	}

	function getDb(){
		return $this->db;
	}

	function setDb($db){
		$this->db = $db;
	}

	/** カートをIPアドレスで制限 **/
	function banIPAddress($pluginId){
		$this->setAttribute("payment_option_page_display_count", null);

		$cartDao = SOY2DAOFactory::create("cart.SOYShop_BanIpAddressDAO");
		$banObj = new SOYShop_BanIpAddress();
		$banObj->setIpAddress($_SERVER['REMOTE_ADDR']);
		$banObj->setPluginId($pluginId);
		try{
			$cartDao->insert($banObj);
			$this->sendNoticeCartErrorMail("IPアドレスのカートの使用制限をしました：" . $banObj->getIpAddress());
		}catch(Exception $e){
			self::log("禁止するIP Addressの登録に失敗しました。");
		}
	}

	function checkBanIpAddress(){
		return SOY2DAOFactory::create("cart.SOYShop_BanIpAddressDAO")->checkBanByIpAddressAndUpdate($_SERVER['REMOTE_ADDR']);
	}

	function getPaymentOptionPageDisplayCount(){
		$v = $this->getAttribute("payment_option_page_display_count");
		if(is_null($v)) $v = array(0, time());	//第一引数に回数、第二引数に最後に開いた時刻を記録

		// 一時間以内に開いたのであれば回数を増やす。@ToDo 管理画面で設定できるようにしたい
		if((int)$v[1] - 1 * 60 * 60 >= time()){
			$v = array(1, time());	//リセット
		}else{
			$v = array($v[0] + 1, time());
		}

		//記録
		$this->setAttribute("payment_option_page_display_count", $v);

		return (int)$v[0];
	}

	public function log($text){
		error_log("[".$this->getAttribute("page")."] ".$text);
	}
}

/* 在庫切れ */
class SOYShop_StockException extends Exception{}
class SOYShop_EmptyStockException extends SOYShop_StockException{}
class SOYShop_OverStockException extends SOYShop_StockException{}

class SOYShop_CartException extends Exception{}
class SOYShop_EmptyCartException extends SOYShop_CartException{}
class SOYShop_AcceptOrderException extends SOYShop_CartException{}

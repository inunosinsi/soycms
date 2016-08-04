<?php
/**
 * @class Cart03Page
 * @date 2009-07-16T17:00:20+09:00
 * @author SOY2HTMLFactory
 */
class Cart03Page extends MainCartPageBase{

	private $user;
	private $userId;
	private $send;

	function doPost(){

		if(isset($_POST["next"]) || isset($_POST["next_x"])){

			$cart = CartLogic::getCart();

			if(!$this->user){
				$this->user = $cart->getCustomerInformation();
			}
			$user = $this->user;

			//まずはエラーチェックのみ
			$this->checkError($cart);

			$moduleDAO = SOY2DAOFactory::create("plugin.SOYShop_PluginConfigDAO");

			/**
			 * 古いのをクリア
			 * ポイントとカスタムフィールドの値はプラグイン内で削除
			 */
			$cart->removeModule($cart->getAttribute("payment_module"));
			$cart->removeModule($cart->getAttribute("delivery_module"));
			$cart->clearAttribute("payment_module");
			$cart->clearAttribute("delivery_module");

			SOYShopPlugin::load("soyshop.discount");
			SOYShopPlugin::invoke("soyshop.discount", array(
				"mode" => "clear",
				"cart" => $cart,
			));

			SOYShopPlugin::load("soyshop.point.payment");
			SOYShopPlugin::invoke("soyshop.point.payment", array(
				"mode" => "clear",
				"cart" => $cart,
			));

			SOYShopPlugin::load("soyshop.order.customfield");
			SOYShopPlugin::invoke("soyshop.order.customfield", array(
				"mode" => "clear",
				"cart" => $cart,
			));

			//支払
			if(isset($_POST["payment_module"]) && !$cart->hasError("payment")){
				//選択を保存
				$moduleId = $_POST["payment_module"];
				$cart->setAttribute("payment_module", $moduleId);

				//選択されたプラグインのみを読み込む：plugins/$moduleId/soyshop.payment.php
				$paymentModule = $moduleDAO->getByPluginId($moduleId);
				SOYShopPlugin::load("soyshop.payment", $paymentModule);

				//実行
				SOYShopPlugin::invoke("soyshop.payment", array(
					"mode" => "select",
					"cart" => $cart
				));
			}

			//配送
			if(isset($_POST["delivery_module"]) && !$cart->hasError("delivery")){
				$moduleId = $_POST["delivery_module"];
				$cart->setAttribute("delivery_module", $moduleId);

				//選択されたプラグインのみを読み込む
				$deliveryModule = $moduleDAO->getByPluginId($moduleId);
				SOYShopPlugin::load("soyshop.delivery", $deliveryModule);

				SOYShopPlugin::invoke("soyshop.delivery", array(
					"mode" => "select",
					"cart" => $cart
				));
			}

			//割引
			if(!$cart->hasError("discount") && isset($_POST["discount_module"])){
				
				//全部ロードする
				SOYShopPlugin::load("soyshop.discount");
				SOYShopPlugin::invoke("soyshop.discount", array(
					"mode" => "select",
					"cart" => $cart,
					"param" => $_POST["discount_module"]
				));				
			}

			/**
			 * ポイント
			 * 念のため、顧客IDがあるかどうかですでに登録されているか？を見ておく
			 */
			if(!$cart->hasError("point") && (isset($_POST["point_module"])) && !is_null($user->getId())){
				//全部ロードする
				SOYShopPlugin::load("soyshop.point.payment");
				SOYShopPlugin::invoke("soyshop.point.payment", array(
					"mode" => "select",
					"cart" => $cart,
					"param" => $_POST["point_module"],
					"userId" => $user->getId()
				));
			}

			//カスタムフィールド
			if(isset($_POST["customfield_module"])){
				//ロードしない？
//				SOYShopPlugin::load("soyshop.order.customfield");
				SOYShopPlugin::invoke("soyshop.order.customfield", array(
					"mode" => "post",
					"cart" => $cart,
					"param" => $_POST["customfield_module"]
				));
			}
			
			//上記の処理以外で行いたいことがあればここで行う
			SOYShopPlugin::load("soyshop.order.process");
			SOYShopPlugin::invoke("soyshop.order.process", array(
				"mode" => "cart03post",
				"cart" => $cart
			));

			//エラーがなければ次へ
			if($cart->hasError()){
				$cart->setAttribute("page", "Cart03");
			}else{
				$cart->setAttribute("page", "Cart04");
			}

			$cart->save();

			soyshop_redirect_cart();
		}

		if(isset($_POST["prev"]) || isset($_POST["prev_x"])){
			$cart = CartLogic::getCart();
			$cart->setAttribute("page", "Cart02");

			//戻るときにモジュールを削除しない：入力内容を保持しておく
//			$cart->clearAttribute("payment_module");
//			$cart->clearAttribute("delivery_module");

			$cart->clearErrorMessage();

			$cart->save();

			soyshop_redirect_cart();
		}

	}

	function Cart03Page(){
		SOYShopPlugin::active("soyshop.payment");
		SOYShopPlugin::active("soyshop.delivery");
		SOYShopPlugin::active("soyshop.discount");
		SOYShopPlugin::active("soyshop.point.payment");
		SOYShopPlugin::active("soyshop.order.customfield");

		$cart = CartLogic::getCart();
		$this->user = $cart->getCustomerInformation();
		$this->send = $cart->getAddress();

		WebPage::WebPage();

		$this->addForm("order_form", array(
			"action" => soyshop_get_cart_url(false)
		));

		//商品リストの出力
		$items = $cart->getItems();

		$this->createAdd("item_list", "_common.ItemListComponent", array(
			"list" => $items
		));
		
		$this->createAdd("module_list", "_common.ModuleListComponent", array(
			"list" => $cart->getModules()
		));

		$this->buildForm($cart);
		
		$this->addModel("is_subtotal", array(
			"visible" => (SOYSHOP_CART_IS_TAX_MODULE)
		));

		$this->createAdd("total_item_price", "NumberFormatLabel", array(
			"text" => $cart->getItemPrice()
		));

		$this->createAdd("total_price", "NumberFormatLabel", array(
			"text" => $cart->getTotalPrice()
		));

		//ユーザ情報の出力
		$this->outputUser($cart);
		
		$this->addExtensions($cart);

		//エラー周り
		if(!$cart->hasError()){
			DisplayPlugin::hide("has_error");
		}
		$this->appendErrors($cart);
		
		$cart->clearErrorMessage();
	}

	function buildForm(CartLogic $cart){
		
		$user = $this->user;
		
		$paymentMethodList = $this->getPaymentMethod($cart);
		$this->addModel("has_payment_method", array(
			"visible" => (count($paymentMethodList))
		));

		$this->createAdd("payment_method_list", "_common.PaymentMethodListComponent", array(
			"list" => $paymentMethodList,
			"selected" => $cart->getAttribute("payment_module")
		));

		$deliveryMethodList = $this->getDeliveryMethod($cart);
		$this->addModel("has_delivery_method", array(
			"visible" => (count($deliveryMethodList))
		));
		$this->createAdd("delivery_method_list", "_common.DeliveryMethodListComponent", array(
			"list" => $deliveryMethodList,
			"selected" => $cart->getAttribute("delivery_module")
		));

		$discountModuleList = $this->getDiscountMethod($cart);
		$this->addModel("has_discount_method", array(
			"visible" => (count($discountModuleList) > 0),
		));
		$this->createAdd("discount_method_list", "_common.DiscountMethodListComponent", array(
			"list" => $discountModuleList,
		));

		//入力したユーザがポイントを持っているか？
		$hasPoint = $this->hasPointByUserMailAddress($cart, $user->getMailAddress());
		$pointModuleList = $this->getPointMethod($cart);
		$this->addModel("has_point_method", array(
			"visible" => (count($pointModuleList) > 0 && $hasPoint),
		));
		$this->createAdd("point_method_list", "_common.PointMethodListComponent", array(
			"list" => $pointModuleList,
		));

		$customfieldModuleList = $this->getCustomfieldMethod($cart);
		$this->addModel("has_customfield_method", array(
			"visible" => (count($customfieldModuleList) > 0),
		));
		$this->createAdd("customfield_method_list", "_common.CustomfieldMethodListComponent", array(
			"list" => $customfieldModuleList,
		));


		$this->addLabel("myMessage", array(
			"text" => "",
		));
	}

	function outputUser(CartLogic $cart){

		$user = $this->user;
		$send = $this->send;

		$this->addLabel("user_name", array(
			"text" => $user->getName()
		));

		$this->addLabel("user_reading", array(
			"text" => $user->getReading()
		));

		$this->addLabel("send_name", array(
			"text" => (isset($send["name"])) ? $send["name"] : ""
		));

		$this->addLabel("send_reading", array(
			"text" => (isset($send["reading"])) ? $send["reading"] : ""
		));

		$this->addLabel("send_zip_code", array(
			"text" => (isset($send["zipCode"])) ? $send["zipCode"] : ""
		));

		$this->addLabel("send_area", array(
			"text" => (isset($send["area"])) ? SOYShop_Area::getAreaText($send["area"]) : ""
		));

		$this->addLabel("send_address1", array(
			"text" => (isset($send["address1"])) ? $send["address1"] : ""
		));

		$this->addLabel("send_address2", array(
			"text" => (isset($send["address2"])) ? $send["address2"] : ""
		));

		$this->addLabel("send_tel", array(
			"text" => (isset($send["telephoneNumber"])) ? $send["telephoneNumber"] : ""
		));

		$this->addModel("is_use_address", array(
			"visible" => false == (
					empty($send["name"]) &&
					empty($send["reading"]) &&
					empty($send["zipCode"]) &&
					empty($send["area"]) &&
					empty($send["address1"]) &&
					empty($send["address2"]) &&
					empty($send["telephoneNumber"])
			)
		));
	}
	
	/**
	 * 表示用拡張ポイント
	 */
	function addExtensions($cart){
		/* カート soyshop.cart */
		SOYShopPlugin::load("soyshop.cart");
		$delegate = SOYShopPlugin::invoke("soyshop.cart", array(
			"mode" => "page03",
			"cart" => $cart
		));

		$html = $delegate->getHtml();
		
		//カートプラグイン 表示/非表示
		$this->addModel("has_cart_plugin", array(
			"visible" => (count($html) > 0)
		));

		$this->createAdd("cart_plugin_list", "_common.CartPluginListComponent", array(
			"list" => $html
		));

		/* ボーナス soyshop.bonus */
		SOYShopPlugin::load("soyshop.bonus");
		$delegate = SOYShopPlugin::invoke("soyshop.bonus", array(
			"mode" => "bonusList",
			"cart" => $cart,
		));
		$bonuses = $delegate->getList();

		//ボーナスプラグイン 表示/非表示
		$this->addModel("has_bonus_plugin", array(
			"visible" => $delegate->getHasBonus()
		));
		
		//ボーナスプラグイン おまけ内容HTML
		$this->createAdd("bonus_plugin_list", "_common.BonusPluginListComponent", array(
			"list" => $bonuses
		));
	}
	
	function getPaymentMethod(CartLogic $cart){

    	//アクティブなプラグインをすべて読み込む
    	SOYShopPlugin::load("soyshop.payment");

		//実行
		$delegate = SOYShopPlugin::invoke("soyshop.payment", array(
			"mode" => "list",
			"cart" => $cart
		));

		return $delegate->getList();
	}

	function getDeliveryMethod(CartLogic $cart){

    	//アクティブなプラグインをすべて読み込む
		SOYShopPlugin::load("soyshop.delivery");

		$delegate = SOYShopPlugin::invoke("soyshop.delivery", array(
			"mode" => "list",
			"cart" => $cart
		));

		return $delegate->getList();
	}

	function getDiscountMethod(CartLogic $cart){

    	//アクティブなプラグインをすべて読み込む
		SOYShopPlugin::load("soyshop.discount");

		$delegate = SOYShopPlugin::invoke("soyshop.discount", array(
			"mode" => "list",
			"cart" => $cart
		));

		return $delegate->getList();
	}

	function getPointMethod(CartLogic $cart){

    	//アクティブなプラグインをすべて読み込む
		SOYShopPlugin::load("soyshop.point.payment");

		$delegate = SOYShopPlugin::invoke("soyshop.point.payment", array(
			"mode" => "list",
			"cart" => $cart,
			"userId" => $this->userId
		));

		return $delegate->getList();
	}

	function getCustomfieldMethod(CartLogic $cart){

    	//アクティブなプラグインをすべて読み込む
		SOYShopPlugin::load("soyshop.order.customfield");

		$delegate = SOYShopPlugin::invoke("soyshop.order.customfield", array(
			"mode" => "list",
			"cart" => $cart
		));

		$obj = array();
		if(is_array($delegate->getList())){
			foreach($delegate->getList() as $list){
				if(is_array($list)){
					foreach($list as $key => $array){
						$obj[$key] = $array;
					}
				}
			}
		}
		return (count($obj) > 0) ? $obj : array();
	}

	/**
	 * ポイントを持っているかチェックする
	 * @param string mailaddress
	 * @return boolean
	 */
	function hasPointByUserMailAddress(CartLogic $cart, $mailaddress){

		//ログインしているかも確認する
		if(!$cart->getAttribute("logined")) return false;

		//ポイント設定プラグインがCMS内に配置されているか？
		$pointPluginDir = dirname(dirname(dirname(dirname(__FILE__)))) . "/module/plugins/common_point_base";
		if(is_dir($pointPluginDir) == false) return false;

		$this->userId = $this->getUserIdByMailAddress($mailaddress);

		try{
			SOY2::import("module.plugins.common_point_base.domain.SOYShop_Point");
			SOY2::import("module.plugins.common_point_base.domain.SOYShop_PointDAO");
			$pointDao = SOY2DAOFactory::create("SOYShop_PointDAO");
			$point = $pointDao->getByUserId($this->userId);
		}catch(Exception $e){
			return false;
		}

		$hasPoint = (int)$point->getPoint();

		//有効期限が切れていないかどうか
		if($hasPoint > 0){
			$timeLimit = $point->getTimeLimit();
			if(!is_null($timeLimit) && $timeLimit < time()){
				return false;
			}
		}else{
			return false;
		}
		return true;
	}

	/**
	 * メールアドレスからユーザIDを取得する
	 * @param string mailaddress
	 * @return int user_id
	 */
	function getUserIdByMailAddress($mailaddress){
		$userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
		try{
			$user = $userDao->getByMailAddress($mailaddress);
		}catch(Exception $e){
			$user = new SOYShop_User();
		}

		return (int)$user->getId();
	}

	/**
	 * エラー周りを設定
	 */
	function appendErrors(CartLogic $cart){

		$this->createAdd("payment_error", "ErrorMessageLabel", array(
			"text" => $cart->getErrorMessage("payment")
		));

		if(strlen($cart->getErrorMessage("payment")) < 1) DisplayPlugin::hide("has_payment_error");

		$this->createAdd("delivery_error", "ErrorMessageLabel", array(
			"text" => $cart->getErrorMessage("delivery")
		));

		if(strlen($cart->getErrorMessage("delivery")) < 1) DisplayPlugin::hide("has_delivery_error");
		
		$this->createAdd("point_error", "ErrorMessageLabel", array(
			"text" => $cart->getErrorMessage("point")
		));

		if(strlen($cart->getErrorMessage("point")) < 1) DisplayPlugin::hide("has_point_error");
	}

	/**
	 * @return boolean
	 */
	function checkError(CartLogic $cart){

		$res = false;

		$deliveryMethodList = $this->getDeliveryMethod($cart);
		if(count($deliveryMethodList) > 0){
			if(!isset($_POST["payment_module"]) || strlen($_POST["payment_module"]) < 1){
				$cart->addErrorMessage("payment", MessageManager::get("PAYMENT_NO_SELECT"));
				$res = true;
			}else{
				$cart->removeErrorMessage("payment");
			}
		}

		$deliveryMethodList = $this->getDeliveryMethod($cart);
		if(count($deliveryMethodList) > 0){
			if(!isset($_POST["delivery_module"]) || strlen($_POST["delivery_module"]) < 1){
				$cart->addErrorMessage("delivery", MessageManager::get("DELIVERY_NO_SELECT"));
				$res = true;
			}else{
				$cart->removeErrorMessage("delivery");
			}
		}
			

		//Discount Module
		if(isset($_POST["discount_module"])){
			SOYShopPlugin::load("soyshop.discount");
			$delegate = SOYShopPlugin::invoke("soyshop.discount", array(
				"mode" => "checkError",
				"cart" => $cart,
				"param" => $_POST["discount_module"]
			));

			if($delegate->hasError()){
				$cart->addErrorMessage("discount", MessageManager::get("DISCOUNT_ERROR"));
				$res = true;
			}else{
				$cart->removeErrorMessage("discount");
			}
		}

		//Point Module
		if(isset($_POST["point_module"])){
			SOYShopPlugin::load("soyshop.point.payment");
			$delegate = SOYShopPlugin::invoke("soyshop.point.payment", array(
				"mode" => "checkError",
				"cart" => $cart,
				"param" => $_POST["point_module"]
			));

			if($delegate->hasError()){
				$cart->addErrorMessage("point", MessageManager::get("POINT_ERROR"));
				$res = true;
			}else{
				$cart->removeErrorMessage("point");
			}
		}

		//Customfield Module
		SOYShopPlugin::load("soyshop.order.customfield");
		$delegate = SOYShopPlugin::invoke("soyshop.order.customfield", array(
			"mode" => "checkError",
			"cart" => $cart,
			"param" => (isset($_POST["customfield_module"])) ? $_POST["customfield_module"] : array()
		));

		if($delegate->hasError()){
			$cart->addErrorMessage("customfield", MessageManager::get("CUSTOMFIELD_ERROR"));
			$res = true;
		}else{
			$cart->removeErrorMessage("customfield");
		}
		

		return $res;
	}
}
?>
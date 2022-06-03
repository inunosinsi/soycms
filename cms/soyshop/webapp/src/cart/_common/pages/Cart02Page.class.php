<?php
/**
 * @class Cart02Page
 * @date 2009-07-16T16:25:28+09:00
 * @author SOY2HTMLFactory
 */
class Cart02Page extends MainCartPageBase{

	public $component;
	public $backward;

	function doPost(){

		if(soy2_check_token() && soy2_check_referer()){
			$cart = CartLogic::getCart();

			//soyshop.cart.php拡張ポイント側でdoPost02を持つ
			SOYShopPlugin::invoke("soyshop.cart", array(
				"mode" => "doPost02",
				"cart" => $cart
			));

			if(isset($_POST["next"]) || isset($_POST["next_x"])){

				// 隠しモード クーポン
				SOYShopPlugin::load("soyshop.discount");
				SOYShopPlugin::invoke("soyshop.discount", array(
					"mode" => "clear",
					"cart" => $cart,
				));

				//ユーザー情報
				self::_setCustomerInformation($cart);

				//ユーザカスタムフィールドの値をセッションに入れる
				if(isset($_POST["user_customfield"]) || isset($_POST["user_custom_search"])){
					SOYShopPlugin::load("soyshop.user.customfield");
					SOYShopPlugin::invoke("soyshop.user.customfield",array(
						"mode" => "post",
						"app" => $cart,
						"param" => (isset($_POST["user_customfield"]) && is_array($_POST["user_customfield"])) ? $_POST["user_customfield"] : array()
					));
				}

				//宛先
				$validAddress = self::_setAddress($cart);

				//備考
				if(isset($_POST["Attributes"]) && isset($_POST["Attributes"]["memo"])){
					$cart->setOrderAttribute("memo", MessageManager::get("NOTE"), $_POST["Attributes"]["memo"]);
				}

				//割引 Cart02でもクーポンを使用できるようにする　Cart02でクーポンを使用したい場合はCart03でhiddenで渡す
				if(!$cart->hasError("discount") && isset($_POST["discount_module"])){

					//全部ロードする
					SOYShopPlugin::load("soyshop.discount");
					SOYShopPlugin::invoke("soyshop.discount", array(
						"mode" => "select",
						"cart" => $cart,
						"param" => $_POST["discount_module"]
					));
				}

				//エラーがなければ次へ
				if($validAddress && self::_checkError($cart)){
					$cart->setAttribute("prev_page", "Cart02");
					$cart->setAttribute("page", "Cart03");
				}else{
					$cart->setAttribute("page", "Cart02");
				}

				//宛先の情報を入れておく
				$user = $cart->getCustomerInformation();

				$addr = (isset($_POST["Address"])) ? $_POST["Address"] : array();
				$user->setAddressList(array($addr));


				$cart->save();
				soyshop_redirect_cart();
			}

			if(isset($_POST["prev"]) || isset($_POST["prev_x"])){
				SOYShopPlugin::invoke("soyshop.cart", array(
					"mode" => "afterOperation",
					"cart" => $cart
				));

				$cart->setAttribute("page", "Cart01");
				$cart->save();
				soyshop_redirect_cart();
			}
		}

		//郵便番号での住所検索
		if(isset($_POST["user_zip_search"]) || isset($_POST["send_zip_search"])){
			$logic = SOY2Logic::createInstance("logic.cart.AddressSearchLogic");

			$customer = (object)$_POST["Customer"];
			$user = SOY2::cast("SOYShop_User", $customer);

			//宛先
			$addr = $_POST["Address"];

			//備考
			if(isset($_POST["Attributes"]) && isset($_POST["Attributes"]["memo"])){
				$cart->setOrderAttribute("memo", MessageManager::get("NOTE"), $_POST["Attributes"]["memo"]);
			}

			if(isset($_POST["user_zip_search"])){
				$code = soyshop_cart_address_validate($user->getZipcode());
				$res = $logic->search($code);
				$user->setArea(SOYShop_Area::getAreaByText($res["prefecture"]));
				$user->setAddress1($res["address1"]);
				$user->setAddress2($res["address2"]);
				$anchor = "zipcode1";

			}else{
				$code = soyshop_cart_address_validate($addr["zipCode"]);
				$res = $logic->search($code);
				$addr["area"] = SOYShop_Area::getAreaByText($res["prefecture"]);
				$addr["address1"] = $res["address1"];
				$addr["address2"] = $res["address2"];
				$anchor = "zipcode2";
			}

			$cart->setAttribute("address_key", 0);
			$user->setAddressList(array($addr));
			$cart->setCustomerInformation($user);
			$cart->save();

			soyshop_redirect_cart_with_anchor($anchor);
		}

		soyshop_redirect_cart();
	}

	function __construct(){
		SOYShopPlugin::load("soyshop.cart");

		$this->backward = new BackwardUserComponent();
		$this->component = new UserComponent();

		parent::__construct();

		$cart = CartLogic::getCart();
		$items = $cart->getItems();

		$html = SOYShopPlugin::invoke("soyshop.cart", array(
			"mode" => "upper02",
			"cart" => $cart
		))->getHtml();

		$this->addModel("has_cart_plugin_upper_parts", array(
			"visible" => (count($html) > 0)
		));

		$this->createAdd("cart_plugin_upper_parts_list", "_common.CartPluginListComponent", array(
			"list" => $html
		));

		$this->addForm("order_form", array(
			"action" => soyshop_get_cart_url(false)
		));

		//カスタマイズ用で予備のフォームタグを用意する
		$this->addForm("custom_order_form", array(
			"action" => soyshop_get_cart_url(false)
		));

		$this->createAdd("item_list", "_common.ItemListComponent", array(
			"list" => $items
		));

		$this->addModel("is_subtotal", array(
			"visible" => (SOYSHOP_CART_IS_TAX_MODULE)
		));

		$this->createAdd("total_item_price", "NumberFormatLabel", array(
			"text" => $cart->getItemPrice()
		));

		$this->createAdd("total_price", "NumberFormatLabel", array(
			"text" => $cart->getTotalPrice()
		));

		$customer = $cart->getCustomerInformation();

		self::_buildForm($cart, $customer);	//顧客情報フォーム
		self::_buildSendForm($cart, $customer);	//送付先フォーム

		//zip2address_js
		$this->addModel("zip2address_js", array(
			"src" => soyshop_get_zip_2_address_js_filepath()
		));

		$html = SOYShopPlugin::invoke("soyshop.cart", array(
			"mode" => "page02",
			"cart" => $cart
		))->getHtml();


		$this->addModel("has_cart_plugin", array(
			"visible" => (count($html) > 0)
		));

		$this->createAdd("cart_plugin_list", "_common.CartPluginListComponent", array(
			"list" => $html
		));

		//エラー周り
		DisplayPlugin::toggle("has_error", $cart->hasError());
		self::_appendErrors($cart);

		$cart->clearErrorMessage();
	}

	/**
	 * @param CartLogic $mypage
	 * @param SOYShop_User $user
	 * @param string $mode ユーザカスタムフィールドのモード指定
	 */
	private function _buildForm(CartLogic $cart, SOYShop_User $user, $mode=UserComponent::MODE_CUSTOM_FORM){
		//共通コンポーネントに移し替え  soyshop/component/UserComponent.class.php buildFrom()
		//後方互換性確保は soyshop/component/backward/BackwardUserComponent

		//以前のフォーム 後方互換
		$this->backward->backwardCartRegister($this, $user);

		//共通フォーム
		$this->component->buildForm($this, $user, $cart, $mode);

		//割引モジュール 隠しモード
		$discountModuleList = parent::getDiscountMethod($cart);
		$this->addModel("has_discount_method", array(
			"visible" => (count($discountModuleList) > 0),
		));
		$this->createAdd("discount_method_list", "_common.DiscountMethodListComponent", array(
			"list" => $discountModuleList,
		));
	}

	/**
	 * お届け先フォーム
	 */
	private function _buildSendForm(CartLogic $cart, SOYShop_User $customer){

		$cnf = SOYShop_ShopConfig::load();

		//お届け先情報のフォームを表示するか？
		$this->addModel("display_send_form", array(
			"visible" => ($cnf->getDisplaySendInformationForm())
		));

		$addr = ($cart->isUseCutomerAddress()) ? $cart->getAddress() : $cart->getCustomerInformation()->getEmptyAddressArray();
		self::_buildCompatibleSendForm($addr);
		
		$displayCnf = $cnf->getSendAddressDisplayFormConfig();
		$requiredCnf = $cnf->getSendAddressInformationConfig();
		$reqTxt = $cnf->getRequireText();
		foreach($displayCnf as $key => $bool){
			$this->addModel("send_" . $key . "_show", array(
				"visible" => $bool
			));

			$this->addInput("send_" . $key, array(
				"name" => "Address[" . $key . "]",
				"value" => (isset($addr[$key])) ? $addr[$key] : "",
			));	

			$isReq = (isset($requiredCnf[$key]) && $requiredCnf[$key]);
			$this->addLabel("send_" . $key . "_required", array(
				"html" => ($isReq) ? $reqTxt : "",
				"attr:class" => ($isReq) ? "require" : ""
			));
		}
		
    	$this->addSelect("send_area", array(
    		"name" => "Address[area]",
    		"options" => SOYShop_Area::getAreas(),
    		"selected" => (isset($addr["area"]) && is_numeric($addr["area"])) ? (int)$addr["area"] : 0,
    	));

		SOY2::import("util.SOYShopAddressUtil");
		$addressItems = SOYShopAddressUtil::getAddressItems();
		for($i = 1; $i <= 4; $i++){
			$itemCnf = (isset($addressItems[$i - 1])) ? $addressItems[$i - 1] : SOYShopAddressUtil::getEmptyAddressItem();

			$this->addModel("send_address" . $i . "_show", array(
				"visible" => (isset($itemCnf["label"]) && strlen($itemCnf["label"]))
			));

			$this->addInput("send_address" . $i, array(
				"name" => "Address[address" . $i . "]",
				"value" => (isset($addr["address" . $i])) ? $addr["address" . $i] : "",
			));

			foreach(array("label", "example") as $l){
				$this->addLabel("send_address" . $i . "_" . $l, array(
					"html" => (isset($itemCnf[$l])) ? $itemCnf[$l] : ""
				));
			}
		}

		//法人(勤務先等)を表示するか？
		$this->addModel("is_office_item", array(
			"visible" => $cnf->getDisplayUserOfficeItems()
		));

    	$this->addInput("send_office", array(
    		"name" => "Address[office]",
    		"value" => (isset($addr["office"])) ? $addr["office"] : "",
    	));

    	$memo = $cart->getOrderAttribute("memo");
    	if(is_null($memo)) $memo = array("name" => MessageManager::get("NOTE"), "value" => "");
    	$this->addTextArea("order_memo", array(
    		"name" => "Attributes[memo]",
    		"value" => (isset($memo["value"])) ? $memo["value"] : ""
    	));
	}

	/**
	 * 一部soy:idで互換性をもたせる
	 * @param array
	 */
	private function _buildCompatibleSendForm(array $addr){
		$this->addInput("send_furigana", array(
    		"name" => "Address[reading]",
    		"value" => (isset($addr["reading"])) ? $addr["reading"] : "",
    	));

    	$this->addInput("send_post_number", array(
    		"name" => "Address[zipCode]",
    		"value" => (isset($addr["zipCode"])) ? $addr["zipCode"] : "",
    	));

		$this->addInput("send_tel_number", array(
    		"name" => "Address[telephoneNumber]",
    		"value" => (isset($addr["telephoneNumber"])) ? $addr["telephoneNumber"] : "",
    	));
	}

	/**
	 * エラー周りを設定
	 */
	private function _appendErrors(CartLogic $cart){
		//共通エラーメッセージ
		$this->component->appendErrors($this, $cart);
	}

	/**
	 * 入力内容を確認する
	 * エラーがなければtrue
	 * @return boolean
	 */
	private function _checkError(CartLogic $cart){
		$user = $cart->getCustomerInformation();

		$res = true;
		$cart->clearErrorMessage();

		//共通エラーチェック
		$res = $this->component->checkError($user, $cart, UserComponent::MODE_CART_REGISTER);

		//隠しモード Discount Module
		if(isset($_POST["discount_module"])){
			SOYShopPlugin::load("soyshop.discount");
			$delegate = SOYShopPlugin::invoke("soyshop.discount", array(
				"mode" => "checkError",
				"cart" => $cart,
				"param" => $_POST["discount_module"]
			));

			if($delegate->hasError()){
				$cart->addErrorMessage("discount", MessageManager::get("DISCOUNT_ERROR"));
				$res = false;
			}else{
				$cart->removeErrorMessage("discount");
			}
		}

		return $res;
	}

	/**
	 * カートにPOSTされて顧客情報をセットする
	 * @param CartLogic
	 */
	private function _setCustomerInformation(CartLogic $cart){
		$user = new SOYShop_User();

		//POSTデータ
		$customer = $_POST["Customer"];
		$customer = $this->component->adjustUser($customer);

		$customer = (object)$customer;

		//既存ユーザー
		try{
			if($cart->getAttribute("logined") && $cart->getAttribute("logined_userid")){
				/*
				 * ログインしている場合
				 */
				try{
					$user = soyshop_get_user_object($cart->getAttribute("logined_userid"));
				}catch(Exception $e){
					$user = $cart->getCustomerInformation();
				}

				//もし、ログインしていたユーザとメールアドレスが異なる場合は別ユーザとして登録する
				if($customer->mailAddress != $user->getMailAddress()){
					$cart->clearAttribute("logined");
					$cart->clearAttribute("logined_userid");
				}
			}else{
				/*
				 * ログインしていない場合：パスワードのチェックは不要
				 */
			}
			//パスワードは削除しておく
			$user->setPassword(null);
		}catch(Exception $e){
			//$cart->addErrorMessage("dao_error","データベースに接続できません。");
		}

		//POSTの値で上書き
		SOY2::cast($user,$customer);

		//宛先
		$addr = (isset($_POST["Address"])) ? $_POST["Address"] : array();
		$cart->setAttribute("address_key", 0);
		$user->setAddressList(array($addr));

		$cart->setCustomerInformation($user);
	}

	/**
	 * カートにPOSTされたお届け先情報をセットする
	 * @return Boolean
	 */
	private function _setAddress(CartLogic $cart){
		$user = $cart->getCustomerInformation();

		if(isset($_POST["Address"])){
			$addr = $_POST["Address"];
			$addr["name"] = $this->_trim($addr["name"]);
			$addr["reading"] = $this->convertKana($addr["reading"]);
			$res = $user->checkValidAddress($addr);
			$validAddress = true;
		}else{
			$res = -1;
			$validAddress = true;	//無条件でtrue

		}


		if($res < 0){
			//宛先が入力されていないので顧客の連絡先を使う
			$cart->clearAttribute("address_key");
		}else{
			$cart->setAttribute("address_key", 0);
			$user->setAddressList(array($addr));
			if(!$res){
				//宛先の入力エラー
				$validAddress = false;
				$cart->addErrorMessage("send_address", MessageManager::get("SEND_ADDRESS_ERROR"));
			}
		}

		$cart->setCustomerInformation($user);

		return $validAddress;
	}
}

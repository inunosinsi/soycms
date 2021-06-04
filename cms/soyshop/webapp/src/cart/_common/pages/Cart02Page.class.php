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
				$this->setCustomerInformation($cart);

				//ユーザカスタムフィールドの値をセッションに入れる
				if(isset($_POST["user_customfield"]) || isset($_POST["user_custom_search"])){
					SOYShopPlugin::load("soyshop.user.customfield");
					SOYShopPlugin::invoke("soyshop.user.customfield",array(
						"mode" => "post",
						"app" => $cart,
						"param" => $_POST["user_customfield"]
					));
				}

				//宛先
				$validAddress = $this->setAddress($cart);

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
				if($validAddress && self::checkError($cart)){
					$cart->setAttribute("prev_page", "Cart02");
					$cart->setAttribute("page", "Cart03");
				}else{
					$cart->setAttribute("page", "Cart02");
				}

				//宛先の情報を入れておく
				$user = $cart->getCustomerInformation();

				$address = (isset($_POST["Address"])) ? $_POST["Address"] : array();
				$user->setAddressList(array($address));


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
			$address = $_POST["Address"];

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
				$code = soyshop_cart_address_validate($address["zipCode"]);
				$res = $logic->search($code);
				$address["area"] = SOYShop_Area::getAreaByText($res["prefecture"]);
				$address["address1"] = $res["address1"];
				$address["address2"] = $res["address2"];
				$anchor = "zipcode2";
			}

			$cart->setAttribute("address_key", 0);
			$user->setAddressList(array($address));
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

		//顧客情報フォーム
		$this->buildForm($cart, $customer);

		//送付先フォーム
		$this->buildSendForm($cart, $customer);

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
		$this->appendErrors($cart);

		$cart->clearErrorMessage();
	}

	/**
	 * @param CartLogic $mypage
	 * @param SOYShop_User $user
	 * @param string $mode ユーザカスタムフィールドのモード指定
	 */
	function buildForm(CartLogic $cart, SOYShop_User $user, $mode=UserComponent::MODE_CUSTOM_FORM){
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
	function buildSendForm(CartLogic $cart, SOYShop_User $customer){

		$config = SOYShop_ShopConfig::load();

		//お届け先情報のフォームを表示するか？
		$this->addModel("display_send_form", array(
			"visible" => ($config->getDisplaySendInformationForm())
		));

		$address = ($cart->isUseCutomerAddress()) ? $cart->getAddress() : $cart->getCustomerInformation()->getEmptyAddressArray();

		$this->addInput("send_name", array(
    		"name" => "Address[name]",
    		"value" => (isset($address["name"])) ? $address["name"] : "",
    	));

    	$this->addInput("send_furigana", array(
    		"name" => "Address[reading]",
    		"value" => (isset($address["reading"])) ? $address["reading"] : "",
    	));

    	$this->addInput("send_post_number", array(
    		"name" => "Address[zipCode]",
    		"value" => (isset($address["zipCode"])) ? $address["zipCode"] : "",
    	));

    	$this->addSelect("send_area", array(
    		"name" => "Address[area]",
    		"options" => SOYShop_Area::getAreas(),
    		"value" => $address["area"],
    	));

    	$this->addInput("send_address1", array(
    		"name" => "Address[address1]",
    		"value" => (isset($address["address1"])) ? $address["address1"] : "",
    	));

    	$this->addInput("send_address2", array(
    		"name" => "Address[address2]",
    		"value" => (isset($address["address2"])) ? $address["address2"] : "",
    	));

		$this->addInput("send_address3", array(
    		"name" => "Address[address3]",
    		"value" => (isset($address["address3"])) ? $address["address3"] : "",
    	));

    	$this->addInput("send_tel_number", array(
    		"name" => "Address[telephoneNumber]",
    		"value" => (isset($address["telephoneNumber"])) ? $address["telephoneNumber"] : "",
    	));

		//法人(勤務先等)を表示するか？
		$this->addModel("is_offce_item", array(
			"visible" => $config->getDisplayUserOfficeItems()
		));

    	$this->addInput("send_office", array(
    		"name" => "Address[office]",
    		"value" => (isset($address["office"])) ? $address["office"] : "",
    	));

    	$memo = $cart->getOrderAttribute("memo");
    	if(is_null($memo)) $memo = array("name" => MessageManager::get("NOTE"), "value" => "");
    	$this->addTextArea("order_memo", array(
    		"name" => "Attributes[memo]",
    		"value" => (isset($memo["value"])) ? $memo["value"] : ""
    	));
	}

	/**
	 * エラー周りを設定
	 */
	function appendErrors(CartLogic $cart){
		//共通エラーメッセージ
		$this->component->appendErrors($this, $cart);
	}

	/**
	 * 入力内容を確認する
	 * エラーがなければtrue
	 * @return boolean
	 */
	private function checkError(CartLogic $cart){
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
	 */
	private function setCustomerInformation(CartLogic $cart){
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
		$address = (isset($_POST["Address"])) ? $_POST["Address"] : array();
		$cart->setAttribute("address_key", 0);
		$user->setAddressList(array($address));

		$cart->setCustomerInformation($user);
	}

	/**
	 * カートにPOSTされたお届け先情報をセットする
	 * @return Boolean
	 */
	private function setAddress($cart){
		$user = $cart->getCustomerInformation();

		if(isset($_POST["Address"])){
			$address = $_POST["Address"];
			$address["name"] = $this->_trim($address["name"]);
			$address["reading"] = $this->convertKana($address["reading"]);
			$res = $user->checkValidAddress($address);
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
			$user->setAddressList(array($address));
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

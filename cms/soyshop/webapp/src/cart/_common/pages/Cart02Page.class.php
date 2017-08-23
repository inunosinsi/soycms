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

		$cart = CartLogic::getCart();

		if(isset($_POST["next"]) || isset($_POST["next_x"])){

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

			//エラーがなければ次へ
			if($validAddress && $this->checkError($cart)){
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
			soyshop_redirect_cart();
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
	}

	function __construct(){
		SOYShopPlugin::load("soyshop.cart");
		
		$this->backward = new BackwardUserComponent();
		$this->component = new UserComponent();
		
		parent::__construct();

		$this->addForm("order_form", array(
			"action" => soyshop_get_cart_url(false)
		));
		
		//カスタマイズ用で予備のフォームタグを用意する
		$this->addForm("custom_order_form", array(
			"action" => soyshop_get_cart_url(false)
		));

		$cart = CartLogic::getCart();
		$items = $cart->getItems();

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

		$delegate = SOYShopPlugin::invoke("soyshop.cart", array(
			"mode" => "page02",
			"cart" => $cart
		));

		$html = $delegate->getHtml();

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
	}
	
	/**
	 * お届け先フォーム
	 */
	function buildSendForm(CartLogic $cart, SOYShop_User $customer){
		
		//お届け先情報のフォームを表示するか？
		$this->addModel("display_send_form", array(
			"visible" => (SOYShop_ShopConfig::load()->getDisplaySendInformationForm())
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

    	$this->addInput("send_tel_number", array(
    		"name" => "Address[telephoneNumber]",
    		"value" => (isset($address["telephoneNumber"])) ? $address["telephoneNumber"] : "",
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
	function checkError(CartLogic $cart){
		$user = $cart->getCustomerInformation();
		
		$res = true;
		$cart->clearErrorMessage();
		
		//共通エラーチェック
		$res = $this->component->checkError($user, $cart, UserComponent::MODE_CART_REGISTER);
		
		return $res;
	}

	/**
	 * カートにPOSTされて顧客情報をセットする
	 */
	private function setCustomerInformation(CartLogic $cart){
		$userDAO = SOY2DAOFactory::create("user.SOYShop_UserDAO");
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
					$user = $userDAO->getById($cart->getAttribute("logined_userid"));
				}catch(Exception $e){
					$user = $cart->getCustomerInformation();
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
?>
<?php
/**
 * @class Cart04Page
 * @date 2009-07-16T17:06:42+09:00
 * @author SOY2HTMLFactory
 */
class Cart04Page extends MainCartPageBase{

	public $component;
	public $backward;

	function doPost(){

		if(soy2_check_token() && soy2_check_referer()){

			$cart = CartLogic::getCart();
			$cart->removeErrorMessage("order_confirm_error");

			if(isset($_POST["next"]) || isset($_POST["next_x"])){

				//入力内容に間違いがないか？最終チェックのプラグイン
				if(isset($_POST["order_confirm_module"])){
					SOYShopPlugin::load("soyshop.order.confirm");
					$delegate = SOYShopPlugin::invoke("soyshop.order.confirm", array(
						"mode" => "checkError",
						"param" => $_POST["order_confirm_module"]
					));

					if($delegate->hasError()){
						$cart->addErrorMessage("order_confirm_error", MessageManager::get("INPUT_NOTICE"));
						$cart->save();
						soyshop_redirect_cart();
					}
				}

				try{
					//ポイントモジュール
					{
						SOYShopPlugin::load("soyshop.point.payment");
						$delegate = SOYShopPlugin::invoke("soyshop.point.payment", array(
							"mode" => "order",
							"cart" => $cart,
						));
					}

					//割引モジュール
					{
						SOYShopPlugin::load("soyshop.discount");
						$delegate = SOYShopPlugin::invoke("soyshop.discount", array(
							"mode" => "order",
							"cart" => $cart,
						));
					}

					//ボーナスモジュール
					{
						SOYShopPlugin::load("soyshop.bonus");
						$delegate = SOYShopPlugin::invoke("soyshop.bonus", array(
							"mode" => "order",
							"cart" => $cart,
						));
					}

					//プラグインでのカートチェック
					SOYShopPlugin::load("soyshop.cart.check");
					SOYShopPlugin::invoke("soyshop.cart.check", array(
						"mode" => "page04",
						"cart" => $cart,
					));

					//注文実行
					$cart->order();

					//pluginで次の画面があるかどうかチェック
					$hasOption = $cart->getAttribute("has_option");

					if($hasOption){
						$cart->setAttribute("page", "Cart05");
					}else{
						$cart->setAttribute("page", "Complete");
					}

				}catch(SOYShop_EmptyStockException $e){
					$cart->addErrorMessage("stock", MessageManager::get("OUT_OF_STOCK"));
					$cart->setAttribute("page", "Cart01");
					$cart->save();
				}catch(SOYShop_OverStockException $e){
					$cart->addErrorMessage("stock", MessageManager::get("STOCK_SHORTAGES"));
					$cart->setAttribute("page", "Cart01");
					$cart->save();
				}catch(SOYShop_AcceptOrderException $e){
					$cart->addErrorMessage("accept", MessageManager::get("ORDER_NO_ACCEPT"));
					$cart->setAttribute("page", "Cart01");
					$cart->save();
				}catch(Exception $e){
					$cart->log($e);
					if(DEBUG_MODE){
						$cart->addErrorMessage("order_error", MessageManager::get("ORDER_REGISTER_FAIL") . "<pre>" . var_export($e,true) . "</pre>");
					}else{
						$cart->addErrorMessage("order_error", MessageManager::get("ORDER_REGISTER_FAIL"));
					}
					$cart->save();
				}

				soyshop_redirect_cart();
			}

			if(isset($_POST["prev"]) || isset($_POST["prev_x"])){
				$cart->setAttribute("prev_page", "Cart04");
				$cart->setAttribute("page", "Cart03");
				$cart->save();

				soyshop_redirect_cart();
			}
		}
	}

	function __construct(){
		$this->backward = new BackwardUserComponent();
		$this->component = new UserComponent();

		parent::__construct();

		//商品リストの出力
		$cart = CartLogic::getCart();
		$items = $cart->getItems();

		$this->addModel("no_confirm_error", array(
			"visible" => (is_null($cart->getErrorMessage("order_confirm_error")))
		));

		$this->addModel("is_confirm_error", array(
			"visible" => (!is_null($cart->getErrorMessage("order_confirm_error")))
		));
		$this->addLabel("confirm_error", array(
			"text" => $cart->getErrorMessage("order_confirm_error")
		));

		$this->addForm("order_form", array(
			"action" => soyshop_get_cart_url(false)
		));

		$this->createAdd("item_list", "_common.ItemListComponent", array(
			"list" => $items
		));

		$this->createAdd("module_list", "_common.ModuleListComponent", array(
			"list" => $cart->getModules()
		));

		$this->createAdd("total_item_price", "NumberFormatLabel", array(
			"text" => $cart->getItemPrice()
		));

		$this->createAdd("total_price", "NumberFormatLabel", array(
			"text" => $cart->getTotalPrice()
		));

		//顧客情報 テキスト
		$this->buildForm($cart);

		$this->addExtensions($cart);

		//error
		$this->createAdd("order_error", "ErrorMessageLabel", array(
			"html" => $cart->getErrorMessage("order_error")
		));

		if(strlen($cart->getErrorMessage("order_error")) < 1) DisplayPlugin::hide("has_order_error");
		$cart->clearErrorMessage();
	}

	function buildForm(CartLogic $cart){

		//共通フォーム
		$backward = new BackwardUserComponent();
		$backward->backwardCartConfirm($this, $cart->getCustomerInformation());

		//共通フォーム
		$component = new UserComponent();
		$component->buildForm($this, $cart->getCustomerInformation(), $cart, UserComponent::MODE_CUSTOM_CONFIRM);

		//以前の部分
		$user = $cart->getCustomerInformation();

		$this->addLabel("mail_address", array(
    		"text" => $user->getMailAddress(),
    	));

    	$this->addLabel("password", array(
    		"text" => $user->getPassword(),
    	));

    	$this->addLabel("name", array(
    		"text" => $user->getName(),
    	));

    	$this->addLabel("furigana", array(
    		"text" => $user->getReading(),
    	));

		$gender = $user->getGender();
    	$this->addLabel("gender", array(
			"text" => ($gender == SOYShop_User::USER_SEX_MALE) ? MessageManager::get("SEX_MALE") :
			        ( ($gender == SOYShop_User::USER_SEX_FEMALE) ? MessageManager::get("SEX_FEMALE") : "" )
    	));

    	$this->addLabel("birthday", array(
    		"text" => $user->getBirthdayText()
    	));

    	$this->addLabel("post_number", array(
    		"text" => $user->getZipCode()
    	));

		$this->addLabel("area", array(
    		"text" => SOYShop_Area::getAreaText($user->getArea())
    	));

    	$this->addLabel("address1", array(
    		"text" => $user->getAddress1(),
    	));

    	$this->addLabel("address2", array(
    		"text" => $user->getAddress2(),
    	));

    	$this->addLabel("tel_number", array(
    		"text" => $user->getTelephoneNumber(),
    	));

    	$this->addLabel("fax_number", array(
    		"text" => $user->getFaxNumber(),
    	));

    	$this->addLabel("ketai_number", array(
    		"text" => $user->getCellphoneNumber(),
    	));

    	$this->addLabel("job", array(
    		"text" => $user->getJobName(),
    	));

		//お届け先情報のフォームを表示するか？
		$this->addModel("display_send_form", array(
			"visible" => (SOYShop_ShopConfig::load()->getDisplaySendInformationForm())
		));

    	$send = $cart->getAddress();

		$this->addLabel("send_office", array(
			"text" => (isset($send["office"])) ? $send["office"] : ""
		));
		$this->addModel("if_send_office", array(
			"visible" => (isset($send["office"]) && strlen($send["office"]))
		));

		$this->addLabel("send_name", array(
			"text" => $send["name"]
		));

		$this->addLabel("send_reading", array(
			"text" => $send["reading"]
		));

		$this->addLabel("send_zip_code", array(
			"text" => $send["zipCode"]
		));

		$this->addLabel("send_area", array(
			"text" => SOYShop_Area::getAreaText($send["area"])
		));

		$this->addLabel("send_address1", array(
			"text" => $send["address1"]
		));

		$this->addLabel("send_address2", array(
			"text" => $send["address2"]
		));

		$this->addLabel("send_address3", array(
			"text" => $send["address3"]
		));

		$this->addLabel("send_tel", array(
			"text" => $send["telephoneNumber"]
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

		/*
		 * メモ
		 */
		$memo = $cart->getOrderAttribute("memo");
		$this->addLabel("memo", array(
			"html" => (isset($memo["value"])) ? nl2br(htmlspecialchars($memo["value"])) : "",
		));

		/*
		 * 属性 他で表示しているものは削除
		 */
		$attr = $cart->getOrderAttributes();
		unset($attr["memo"]);

		$this->createAdd("order_attribute_list", "_common.OrderAttributeListComponent", array(
			"list" => $attr
		));
	}

	/**
	 * 表示用拡張ポイント
	 */
	function addExtensions($cart){

		/* 購入確認 soyshop.order.confirm */
		SOYShopPlugin::load("soyshop.order.confirm");
		$delegate = SOYShopPlugin::invoke("soyshop.order.confirm", array(
			"mode" => "display",
			"error" => (!is_null($cart->getErrorMessage("order_confirm_error")))
		));

		$displayHtmls = $delegate->getHtml();

		//入力内容確認のチェックボックス
		$this->addModel("no_confirm_plugin", array(
			"visible" => (count($displayHtmls) === 0)
		));

		$this->createAdd("confirm_plugin_list", "_common.CartPluginListComponent", array(
			"list" => $displayHtmls
		));

		/* カート soyshop.cart */
		SOYShopPlugin::load("soyshop.cart");
		$delegate = SOYShopPlugin::invoke("soyshop.cart", array(
			"mode" => "page04",
			"cart" => $cart
		));

		$htmls = $delegate->getHtml();

		$this->addModel("has_cart_plugin", array(
			"visible" => (count($htmls) > 0)
		));

		$this->createAdd("cart_plugin_list", "_common.CartPluginListComponent", array(
			"list" => $htmls
		));

		/* ボーナス soyshop.bonus */
		SOYShopPlugin::load("soyshop.bonus");
		$bonuses = SOYShopPlugin::invoke("soyshop.bonus", array(
			"mode" => "bonusList",
			"cart" => $cart,
		))->getList();

		//ボーナスプラグイン 表示/非表示
		$this->addModel("has_bonus_plugin", array(
			"visible" => count($bonuses)
		));

		//ボーナスプラグイン おまけ内容HTML
		$this->createAdd("bonus_plugin_list", "_common.BonusPluginListComponent", array(
			"list" => $bonuses
		));
	}
}

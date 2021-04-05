<?php
/**
 * @class Cart01Page
 * @date 2009-07-16T14:40:36+09:00
 * @author SOY2HTMLFactory
 */
class Cart01Page extends MainCartPageBase{

	function doPost(){

		$cart = CartLogic::getCart();
		$userArray = (isset($_POST["User"])) ? $_POST["User"] : array();

		//reset info
		$cart->clearErrorMessage();
		$cart->clearNoticeMessage();
		$cart->clearAttribute("logined");
		$cart->clearAttribute("logined_userid");

		//数量変更
		if(isset($_POST["ItemCount"])){
			self::updateItemCount($cart, $_POST["ItemCount"]);
			$cart->removeErrorMessage("stock");
			$cart->save();
		}

		//数量変更のみ
		if(isset($_POST["modify"]) || isset($_POST["modify_x"])){

			SOYShopPlugin::invoke("soyshop.cart", array(
				"mode" => "afterOperation",
				"cart" => $cart
			));

			$cart->save();
			soyshop_redirect_cart();
		}

		if(soy2_check_token() && soy2_check_referer()){
			//ログインして次へ
			if(isset($_POST["login"]) || isset($_POST["login_x"])){

				//ログイン
				if( $user = self::_login($userArray) ){//代入
					//ログイン情報
					$cart->setCustomerInformation($user);
					$cart->setAttribute("logined", true);
					$cart->setAttribute("logined_userid", $user->getId());

					//マイページでもログイン
					$mypage = MyPageLogic::getMyPage();
					$mypage->setAttribute("loggedin", true);
			    	$mypage->setAttribute("userId", $user->getId());
					$mypage->save();
				}else{
					//ログイン失敗または登録なし
					$user = new SOYShop_User();
					$user->setMailAddress($userArray["mailAddress"]);
					$cart->setCustomerInformation($user);

					$cart->addErrorMessage("login_error", MessageManager::get("NOT_LOGIN"));

					$cart->save();
					soyshop_redirect_cart();
				}

				//プラグインでのカートチェック
				SOYShopPlugin::load("soyshop.cart.check");
				SOYShopPlugin::invoke("soyshop.cart.check", array(
					"mode" => "page01",
					"cart" => $cart,
				));

				//在庫エラーがなければ次へ
				try{
					$cart->checkOrderable();
					$cart->checkItemCountInCart();
					$cart->setAttribute("page", "Cart02");
				}catch(SOYShop_StockException $e){
					$cart->setAttribute("page", "Cart01");
				}catch(SOYShop_CartException $e){
					$cart->setAttribute("page", "Cart01");
				}catch(Exception $e){
					//DB error?
				}

				$cart->save();
				soyshop_redirect_cart();
				exit;
			}

			//ログインしないで次へ
			if(isset($_POST["next"]) || isset($_POST["next_x"])){

				//すでにマイページでログインしているならログインする
				if( $user = self::_getMyPageLoggedInUser() ){//代入
					//ログイン情報
					$cart->setCustomerInformation($user);
					$cart->setAttribute("logined", true);
					$cart->setAttribute("logined_userid", $user->getId());
				}

				//プラグインでのカートチェック
				SOYShopPlugin::load("soyshop.cart.check");
				SOYShopPlugin::invoke("soyshop.cart.check", array(
					"mode" => "page01",
					"cart" => $cart,
				));

				//在庫エラーがなければ次へ
				try{
					$cart->checkOrderable();
					$cart->checkItemCountInCart();
					$cart->setAttribute("page", "Cart02");
				}catch(SOYShop_StockException $e){
					$cart->setAttribute("page", "Cart01");
				}catch(Exception $e){
					//DB error?
				}

				$cart->save();
				soyshop_redirect_cart();
				exit;
			}
		}

		soyshop_redirect_cart();
	}

	function __construct(){

		SOYShopPlugin::load("soyshop.cart");

		parent::__construct();

		$cart = CartLogic::getCart();
		$items = $cart->getItems();

		$shopConfig = SOYShop_ShopConfig::load();

		if(count($items) > 0){
			DisplayPlugin::hide("is_empty");

			//Check stock
			try{
				$cart->checkOrderable();
			}catch(SOYShop_OverStockException $e){
				$cart->addErrorMessage("stock", MessageManager::get("STOCK_SHORTAGES"));
			}catch(SOYShop_EmptyStockException $e){
				$cart->addErrorMessage("stock", MessageManager::get("OUT_OF_STOCK"));
			}catch(SOYShop_AcceptOrderException $e){
				$cart->addErrorMessage("accept", MessageManager::get("ORDER_NO_ACCEPT"));
			}catch(Exception $e){
				//DB error?
			}

		}else{
			DisplayPlugin::hide("go_next");
		}

		$this->addForm("order_form", array(
			"action" => soyshop_get_cart_url(false)
		));

		//カスタマイズ用で予備のフォームタグを用意する
		$this->addForm("custom_order_form", array(
			"action" => soyshop_get_cart_url(false)
		));

		//ログインしている場合はログインフォームを表示させない
		$mypage = MyPageLogic::getMyPage();
		$this->addModel("is_loggedin", array(
			"visible" => ($mypage->getIsLoggedin())
		));
		$this->addModel("not_loggedin", array(
			"visible" => ($mypage->getIsLoggedin() === false)
		));

		$this->addForm("login_form", array(
			"action" => soyshop_get_cart_url(false)
		));

		$this->addLink("login_link", array(
			"link" => soyshop_get_mypage_url() . "/login?r=" . soyshop_get_cart_url()
		));

		$this->createAdd("item_list", "_common.ItemListComponent", array(
			"list" => $items,
			"ignoreStock" => $shopConfig->getIgnoreStock()
		));

		$this->addModel("is_subtotal", array(
			"visible" => (SOYSHOP_CART_IS_TAX_MODULE)
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

		$this->addLink("return_link", array(
			"link" => soyshop_get_site_url(true)
		));

		DisplayPlugin::toggle("has_stock_error", (strlen($cart->getErrorMessage("stock")) > 0));
		$this->addLabel("stock_error", array(
			"text" => $cart->getErrorMessage("stock")
		));

		DisplayPlugin::toggle("has_accept_error", (strlen($cart->getErrorMessage("accept")) > 0));
		$this->addLabel("accept_error", array(
			"text" => $cart->getErrorMessage("accept")
		));

		DisplayPlugin::toggle("has_login_error", (strlen($cart->getErrorMessage("login_error")) > 0));
		$this->addLabel("login_error", array(
			"text" => $cart->getErrorMessage("login_error")
		));

		DisplayPlugin::toggle("has_plugin_error", (strlen($cart->getErrorMessage("plugin_error")) > 0));
		$this->addLabel("plugin_error", array(
			"html" => nl2br($cart->getErrorMessage("plugin_error"))
		));

		DisplayPlugin::toggle("has_plugin_notice", (strlen($cart->getNoticeMessage("plugin_notice")) > 0));
		$this->addLabel("plugin_notice", array(
			"html" => nl2br($cart->getNoticeMessage("plugin_notice"))
		));

		//マイページ関連のリンク
		$this->addLink("remind_link", array(
			"link" => soyshop_get_mypage_url() . "/remind/input"
		));

		$this->addLink("register_link", array(
			"link" => soyshop_get_mypage_url() . "/register"
		));

		$user = $cart->getCustomerInformation();

		$this->addInput("login_user_mail_address", array(
			"name" => "User[mailAddress]",
			"value" => ($user) ? $user->getMailAddress() : ""
		));

		$lastInsertedItemId = $cart->getAttribute("last_insert_item");
		$this->addLink("back_link", array(
			"link" => (is_numeric($lastInsertedItemId)) ? soyshop_get_item_detail_link(soyshop_get_item_object($lastInsertedItemId)) : soyshop_get_site_url()
		));

		$this->addExtensions($cart);

		$cart->clearErrorMessage();
		$cart->clearNoticeMessage();
	}

	/**
	 * 表示用拡張ポイント
	 */
	function addExtensions($cart){
		/* カート soyshop.cart */
		$delegate = SOYShopPlugin::invoke("soyshop.cart", array(
			"mode" => "page01",
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


	/**
	 * カート内商品の数量を変更する
	 */
	private function updateItemCount(CartLogic $cart, $itemCount){

		//カートに入っている商品に変更がある場合は、選択されているモジュールをクリアする
		$cart->clearModules();

		//数量の値は自然数のみ
		$count = array();
		foreach($itemCount as $key => $value){
			 if(function_exists("mb_convert_kana")) $value = mb_convert_kana($value, "a");
			 $count[$key] = max(0, (int)$value);
		}

		//拡張ポイントでカートに入れた商品数と商品毎の合計の再計算を行うかを制御する
		SOYShopPlugin::load("soyshop.cart");
		$isUpdate = SOYShopPlugin::invoke("soyshop.cart", array(
			"mode" => "updateItem",
			"cart" => $cart
		))->getIsUpdate();

		if(is_null($isUpdate) || (is_bool($isUpdate) && $isUpdate)){
			foreach($count as $index => $value){
				$cart->updateItem($index, $value);
			}
		}

		//消費税の計算とモジュールの登録
		if(SOYSHOP_CONSUMPTION_TAX_MODE){
			$cart->setConsumptionTax();
		}elseif(SOYSHOP_CONSUMPTION_TAX_INCLUSIVE_PRICING_MODE){
			$cart->setConsumptionTaxInclusivePricing();
		}else{
			//何もしない
		}
	}

	/**
	 * メールアドレスとパスワードでログイン
	 * @return SOYShop_User
	 */
	private function _login($userArray){
		$userDAO = SOY2DAOFactory::create("user.SOYShop_UserDAO");

		try{
			$user = $userDAO->getByMailAddress($userArray["mailAddress"]);

			if($user->checkPassword($userArray["password"])){
				//ログイン成功
		    	return $user;
			}
		}catch(Exception $e){
			//登録なし
		}
		return null;
	}

	/**
	 * マイページでログイン済みかどうか
	 * @return SOYShop_User
	 */
	private function _getMyPageLoggedInUser(){
		$mypage = MyPageLogic::getMyPage();
		if($mypage->getIsLoggedin()){
			try{
				return soyshop_get_user_object($mypage->getAttribute("userId"));
			}catch(Exception $e){
				//do nothing
			}
		}

		return null;
	}
}
